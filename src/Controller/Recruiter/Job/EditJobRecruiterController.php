<?php

namespace App\Controller\Recruiter\Job;

use App\Entity\Company;
use App\Entity\Contrat;
use App\Entity\JobOffer;
use App\Entity\Location;
use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api')]
#[IsGranted('ROLE_RECRUITER')]
class EditJobRecruiterController extends AbstractController
{
    #[Route("/recruiter/edit-job/{id}", name: "job_edit_recruiter", methods: ["POST"])]
    public function edit(EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request, int $id): Response
    {
        $jobOffer = $entityManager->getRepository(JobOffer::class)->find($id);

        if (!$jobOffer) {
            return $this->json('No job found for id', 404);
        }       
        //$content = json_decode($request->getContent(), true);
        $content = $request->request->all();
    
        if (!$content) {
            return $this->json('Missing required data', 400);
        }
        $selectedContrats = $content['contratEdit'];
        foreach ($jobOffer->getContrats() as $contrat) {
            $jobOffer->removeContrat($contrat);
        }
        $contratsCollection = [];
        foreach ($selectedContrats as $selectedContrat) {
            $contrat = $entityManager->getRepository(Contrat::class)->findOneBy(['type' => $selectedContrat]);
            if ($contrat) {
                $contratsCollection[] = $contrat;
            }
        }
        foreach ($contratsCollection as $contrat) {
            $jobOffer->addContrat($contrat);
        }
        $jobOffer->setTitle($content['title']);
        $jobOffer->setDescription($content['description']);
        $jobOffer->setSalary($content['salary']);
        $jobOffer->setDeadlineAt(new \DateTimeImmutable($content['deadline']));

        $jobOffer->setUpdatedAt(new \DateTimeImmutable());

        $category = $jobOffer->getCategory();
        if ($category) {
            $categoryName = $content['categories'];
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
        }
        $company = $jobOffer->getCompany();
        if ($company) {
            $company->setCompanyName($content['company']);
            $company->setWebsite($content['website']);
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
            }
            $company->setCompanyDetail($content['company_detail']);
        }
        $location = $jobOffer->getLocation();
        if ($location) {
            $location->setAddress($content['address']);
            $location->setCity($content['city']);
            $location->setCountry($content['country']);
        }
        try {
            $entityManager->persist($jobOffer);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return $this->json('Editing a new job offer successfully', 200);
    }
}
