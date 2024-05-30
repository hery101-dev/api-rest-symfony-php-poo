<?php

namespace App\Controller\RecommendCategory;

use App\Entity\JobOffer;
use App\Entity\Categories;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted('ROLE_CANDIDATE')]
class RecommendController extends AbstractController
{
    private $jobRepository;
    private $applicationRepository;
    private $entityManager;
    private $categoriesRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->applicationRepository = $this->entityManager->getRepository(Application::class);
        $this->jobRepository = $this->entityManager->getRepository(JobOffer::class);
        $this->categoriesRepository = $this->entityManager->getRepository(Categories::class);
    }


    #[Route('/api/candidate/recommend', name: 'app_recommend_system_jobOffer', methods: ['GET'])]
    public function listRecommended(RequestStack $requestStack)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur connecté', 404);
        }
        $candidate = $this->applicationRepository->findBy(['candidate' =>  $user]);
        if (!$candidate) {
            return $this->json('Aucun candidature trouvé');
        }
    
        $getCategoryApp =  $candidate[0]->getCategoryJob();
        $countCandidate = $this->applicationRepository->count(['categoryJob' =>  $getCategoryApp, 'candidate' =>  $user]);
    
        $computerScienceOffers = [];
        if ($countCandidate >= 2) {
            $allJobs = $this->jobRepository->findByCategoryName($getCategoryApp);
            foreach ($allJobs as $job) {
                $contratsCollection =  $job->getContrats();
                $contratsData = [];
                foreach ($contratsCollection as $contrat) {
                    $contratsData[] = $contrat->getType();
                }
                $location = $job->getLocation();
                $company = $job->getCompany();
                $logoPath = 'assets/upload/image/' . $company->getLogo();
                $baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    
                $jobData = [
                    'id' => $job->getId(),
                    'title' => $job->getTitle(),
                    'description' => $job->getDescription(),
                    'salary' => $job->getSalary(),
                    'createdAt' => $job->getCreatedAt()->format('c'),
                    'deadlineAt' => $job->getDeadlineAt(),
                    'status' => $job->isJobStatus(),
                    'contrat' =>  $contratsData,
                    'address' => $location ? $location->getAddress() : null,
                    'city' => $location ? $location->getCity() : null,
                    'country' => $location ? $location->getCountry() : null,
                    'logo' => $company ?  $baseUrl . '/' . $logoPath : null,
                    'company_name' => $company ? $company->getCompanyName() : null,
                    'website' => $company ? $company->getWebsite() : null,
                    'company_detail' => $company ? $company->getCompanyDetail() : null,
                ];
    
                if ($job->isJobStatus() === true) {
                    $computerScienceOffers[] = $jobData;
                }
            }
        }
    
        return new JsonResponse($computerScienceOffers);
    }
    
}
