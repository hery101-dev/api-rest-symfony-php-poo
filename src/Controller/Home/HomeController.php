<?php

namespace App\Controller\Home;

use App\Entity\JobOffer;
use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    private $listRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->listRepository = $entityManager->getRepository(JobOffer::class);
    }

    #[Route("/job-offer-list", name: "job_offer_list", methods: ["GET"])]
    public function listJob(SerializerInterface $serializer,  RequestStack $requestStack): Response
    {
        $jobOffers =  $this->listRepository->findAll();
        usort($jobOffers, function ($a, $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });
        $data = [];

        foreach ($jobOffers as $jobOffer) {
            $contratsCollection = $jobOffer->getContrats();
            $contratsData = [];
            foreach ($contratsCollection as $contrat) {
                $contratsData[] = $contrat->getType();
            }

            $today = new \DateTime();
            $today->setTime(0, 0, 0);

            $deadlineAt = $jobOffer->getDeadlineAt();
            $deadlineAt->setTime(0, 0, 0);

            if ($deadlineAt == $today) {
                $jobOffer->setJobStatus(false);
            } else {
                $jobOffer->isJobStatus();
            }

            $contratsString = implode(', ', $contratsData);
            $jobData = [
                'id' => $jobOffer->getId(),
                'title' => $jobOffer->getTitle(),
                'description' => $jobOffer->getDescription(),
                'salary' => $jobOffer->getSalary(),
                'createdAt' => $jobOffer->getCreatedAt()->format('c'),
                'deadlineAt' => $jobOffer->getDeadlineAt(),
                'status' => $jobOffer->isJobStatus(),
                'contrat' => $contratsString,
            ];
            $category = $jobOffer->getCategory();
            if ($category) {
                $jobData['category'] = $category->getCategoryName();
            }
            $location = $jobOffer->getLocation();
            if ($location) {
                $jobData['address'] = $location->getAddress();
                $jobData['city'] = $location->getCity();
                $jobData['country'] = $location->getCountry();
            }
            $company = $jobOffer->getCompany();
            if ($company) {
                $logoPath = 'assets/upload/image/' . $company->getLogo();
                $baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();

                $jobData['logo'] = $baseUrl . '/' . $logoPath;
                $jobData['company_name'] = $company->getCompanyName();
                $jobData['website'] = $company->getWebsite();
                $jobData['company_detail'] = $company->getCompanyDetail();
            }
            if ($jobOffer->isJobStatus() === true) {
                $data[] = $jobData;
            }
        }

        $jsonContent = $serializer->serialize($data, 'json');
        return new JsonResponse($jsonContent, 200, [], true);
    }


    #[IsGranted('ROLE_CANDIDATE')]
    #[Route("/api/job-offer-detail/{id}", name: "job_offer_detail", methods: ["GET"])]
    public function getJob(int $id): Response
    {
        $jobOffer =  $this->listRepository->find($id);

        if (!$jobOffer) {
            return $this->json('No job offer found for id' . $id, 404);
        }
        $category = $jobOffer->getCategory();
        if ($category) {
            $category->getCategoryName();
        }
        $company = $jobOffer->getCompany();
        if ($company) {
            $company->getCompanyName() ? $company->getCompanyName() : null;
            $company->getWebsite() ? $company->getWebsite() : null;
            $company->getLogo() ? $company->getLogo() : null;
        }
        $location = $jobOffer->getLocation() ? $jobOffer->getLocation() : null;
        if ($location) {
            $location->getAddress() ? $location->getAddress() : null;
            $location->getCity() ? $location->getCity() : null;
            $location->getStreet() ?  $location->getStreet() : null;
        }
        $contratsCollection = $jobOffer->getContrats();
        $contratsData = [];
        foreach ($contratsCollection as $contrat) {
            $contratsData[] = $contrat->getType();
        }
        $contratsString = implode(', ', $contratsData);
        $url = 'http://localhost:8000/assets/upload/image/';
        $data =  [
            'id' => $jobOffer->getId(),
            'title' =>  $jobOffer->getTitle() ? $jobOffer->getTitle() : null,
            'description' =>  $jobOffer->getDescription() ? $jobOffer->getDescription() : null,
            'salary' => $jobOffer->getSalary() ? $jobOffer->getSalary() : null,
            'contrat' =>    $contratsString ? $contratsString : null,
            'categories' => $category->getCategoryName() ? $category->getCategoryName() : null,
            'company_name' => $company ? $company->getCompanyName() : null,
            'website' => $company ? $company->getWebsite() : null,
            'logo' => $company && $company->getLogo() ? $url . $company->getLogo() : null,
            'address' => ($location instanceof Location) ? $location->getAddress() : null,
            'city' => ($location instanceof Location) ? $location->getCity() : null,
            'street' => ($location instanceof Location) ? $location->getStreet() : null,
            'createdAt' => $jobOffer->getCreatedAt() ? $jobOffer->getCreatedAt() : null,
            'deadlineAt' => $jobOffer->getDeadlineAt() ? $jobOffer->getDeadlineAt() : null,
        ];
        return $this->json($data);
    }
}
