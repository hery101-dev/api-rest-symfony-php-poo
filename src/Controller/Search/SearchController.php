<?php

namespace App\Controller\Search;

use App\Entity\JobOffer;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(
        Request $request,
        JobOfferRepository $jobOfferRepository,
        RequestStack $requestStack,
    ): JsonResponse {

        $job = $request->query->get('keyword');
        if (!$job) {
            return $this->json('No match for job title');
        }

        $data = [];
        $jobs = $jobOfferRepository->findByKeyword($job);
        foreach ($jobs as $value) {
            $contratsCollection =  $value->getContrats();
            $contratsData = [];
            foreach ($contratsCollection as $contrat) {
                $contratsData[] = $contrat->getType();
            }
            $contratsString = implode(', ', $contratsData);
            $category = $value->getCategory();
            $company = $value->getCompany();
            $location = $value->getLocation();
            $baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
            $jobData = [
                'id' => $value->getId(),
                'title' => $value->getTitle(),
                'description' => $value->getDescription(),
                'job_status' => $value->isJobStatus(),
                'contrat' => $contratsString,
                'createdAt' => $value->getCreatedAt(),
                'deadlineAt' => $value->getDeadlineAt(),
                'category' => $category ? $category->getCategoryName() : null,
                'city' => $location ? $location->getCity() : null,
                'address' => $location ? $location->getAddress() : null,
                'companyName' => $company ? $company->getCompanyName() : null,
                'company_detail' => $company ? $company->getCompanyDetail() : null,
                'website' =>  $company ? $company->getWebsite() : null,
                'logo' => $company ? $baseUrl . '/assets/upload/image/' . $company->getLogo() : null,
            ];
            if ($value->isJobStatus()=== true) {
                $data[] = $jobData;
            }
       
        }

        return $this->json($data);
    }



    // #[Route('/search', name: 'app_search', methods: ['GET'])]
    // public function search(Request $request, JobOfferRepository $jobOfferRepository): JsonResponse
    // {
    //     $keyword = $request->query->get('keyword');

    //     if ($keyword !== null) {
    //         $results = $jobOfferRepository->findByKeyword($keyword);

    //         $serializedResults = $this->serializeResults($results);

    //         return new JsonResponse($serializedResults);
    //     }

    //     return new JsonResponse(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
    // }

    // /**
    //  * Serialize the search results to an array.
    //  */
    // private function serializeResults(array $results): array
    // {
    //     $serializedResults = [];

    //     foreach ($results as $result) {
    //         $serializedResults[] = [
    //             'id' => $result->getId(),
    //             'title' => $result->getTitle(),
    //             'description' => $result->getDescription(),
    //             'company' => [
    //                 'id' => $result->getCompany()->getId(),
    //                 'name' => $result->getCompany()->getCompanyName(),
    //             ],
    //             'location' => [
    //                 'id' => $result->getLocation()->getId(),
    //                 'city' => $result->getLocation()->getCity(),
    //                 'address' => $result->getLocation()->getAddress(),
    //             ],
    //         ];
    //     }

    //     return $serializedResults;
    // }
}
