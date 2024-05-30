<?php

namespace App\Controller\Recruiter\Job;

use App\Entity\Company;
use App\Entity\JobOffer;
use App\Entity\Location;
use App\Entity\Categories;
use App\Entity\Contrat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_RECRUITER')]
class CreateJobController extends AbstractController
{
    #[Route('/api/recruiter/create-jobOffer', name: 'app_create_jobOffer', methods: ['POST'])]
    public function createJobOffer(
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger
    ): JsonResponse {

        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('The user does not exist', 404);
        }

        $data = $request->request->all();

        if (!isset($data)) {
            return new JsonResponse(['error' => 'Missing required data.'], 400);
        }

        $jobOffer = new JobOffer;
        $location = new Location;
        $company = new Company;
        $contrat = new Contrat;

        if (isset($data['deadline'])) {
            $deadline = new \DateTimeImmutable($data['deadline']);

            $today = new \DateTime();
            if ($deadline < $today) {
                return new JsonResponse(['error' => 'Deadline is invalid.'], 401);
            }

            $jobOffer->setDeadlineAt($deadline);
            $entityManager->persist($jobOffer);
        }
        $contratNames = json_decode($data['contrats'], true);
        $contrats = [];
        foreach ($contratNames as $contratName) {
            $contrat = $entityManager->getRepository(Contrat::class)->findOneBy(['type' => $contratName]);
            if ($contrat) {
                $contrats[] = $contrat;
            }
        }
        foreach ($contrats as $contrat) {
            $jobOffer->addContrat($contrat);
        }
        $jobOffer->setTitle($data['title']);
        $jobOffer->setDescription($data['description']);
        $jobOffer->setSalary($data['salary']);

        $categoryName = $data['category'];
        if (!isset($categoryName)) {
            return new JsonResponse(['error' => 'Category is null'], 401);
        }
        $existingCategory = $entityManager
            ->getRepository(Categories::class)
            ->findOneBy(['category_name' => $categoryName]);
        if ($existingCategory) {
            $categories = $existingCategory;
            $jobOffer->setCategory($categories);
        }
        $dataCompany = $data['company'];
        if (!$dataCompany) {
            return $this->json('Company is required', 401);
        }
        $company->setCompanyName($dataCompany);
        $company->setWebsite($data['website']);
        $company->setCompanyDetail($data['company_detail']);
        $logoFile = $request->files->get('logo');

        if ($logoFile) {
            $originalFileLogo = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileLogo = $slugger->slug($originalFileLogo);
            $newFileLogo = $safeFileLogo  . '-' . uniqid() . '.' . $logoFile->guessExtension();

            $logoFile->move(
                $this->getParameter('image_directory'),
                $newFileLogo
            );
            $company->setLogo($newFileLogo);
            $entityManager->persist($company);
        }

        $location->setAddress($data['address']);
        $location->setCity($data['city']);
        $location->setCountry($data['country']);

        $jobOffer->setLocation($location);
        $jobOffer->setCompany($company);

        $jobOffer->setUserId($userActuel);

        try {

            $entityManager->persist($jobOffer);
            $entityManager->persist($location);
            $entityManager->persist($company);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse('Created a new job offer successfully', 200);
    }
}
