<?php

namespace App\Controller\Recruiter\Job;

use App\Entity\Company;
use App\Entity\JobOffer;
use App\Entity\Location;
use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api')]
#[IsGranted('ROLE_RECRUITER')]
class JobDetailsController extends AbstractController
{
    private $entityManager;
    private $jobRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->jobRepository = $this->entityManager->getRepository(JobOffer::class);
    }

    #[Route("/recruiter/job-details/{id}", name: "job_details_get", methods: ["GET"])]
    public function jobDetails(int $id, RequestStack $requestStack): Response
    {
        $jobOffer = $this->jobRepository->find($id);

        if (!$jobOffer) {
            return $this->json('Offre introuvable pour' . $id);
        }

        $categoryName = $jobOffer->getCategory() ? $jobOffer->getCategory()->getCategoryName() : null;
        $company = $jobOffer->getCompany();
        $companyName = $company ? $company->getCompanyName() : null;
        $website = $company ? $company->getWebsite() : null;
        $companyDetail = $company ? $company->getCompanyDetail() : null;
        $logo = $company ? $company->getLogo() : null;

        $location = $jobOffer->getLocation();
        $address = $location ? $location->getAddress() : null;
        $city = $location ? $location->getCity() : null;
        $country = $location ? $location->getCountry() : null;

        $contratsCollection = $jobOffer->getContrats();
        $contratsData = [];
        foreach ($contratsCollection as $contrat) {
            $contratsData[] = $contrat->getType();
        }
        $contratsString = implode(', ', $contratsData);
        $logoPath =  $logo ? 'assets/upload/image/' . $logo : null;
        $baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        $data =  [
            'id' => $jobOffer->getId(),
            'title' =>  $jobOffer->getTitle(),
            'description' =>  $jobOffer->getDescription(),
            'salary' => $jobOffer->getSalary(),
            'createdAt' => $jobOffer->getCreatedAt(),
            'logo' => $logoPath ? $baseUrl . '/' . $logoPath : null,
            'contrat' => $contratsString,
            'categories' => $categoryName,
            'company' =>  $companyName,
            'website' => $website,
            'address' =>  $address,
            'city' =>  $city,
            'deadline' => $jobOffer->getDeadlineAt(),
            'country' =>  $country,
            'company_detail' => $companyDetail,
            'jobStatus' => $jobOffer->isJobStatus()
        ];

        return $this->json($data);
    }


    #[Route("/recruiter/{id}/job-delete", name: "app_job_delete_recruiter", methods: ["DELETE"])]
    public function deleteJob(int $id): Response
    {
        try {
            $project = $this->jobRepository->find($id);

            if (!$project) {
                return $this->json('Aucune offre correspondant', 404);
            }

            $this->entityManager->remove($project);
            $this->entityManager->flush();

            return $this->json('Supprimé avec succès');
        } catch (\Exception $e) {
            return $this->json($e->getMessage());
        }
    }
}
