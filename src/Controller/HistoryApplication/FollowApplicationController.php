<?php

namespace App\Controller\HistoryApplication;


use App\Entity\JobOffer;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route('/api')]
#[IsGranted('ROLE_RECRUITER')]
class FollowApplicationController extends AbstractController
{
    private $entityManager;
    private $applicationRepository;
    private $jobRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->applicationRepository = $entityManager->getRepository(Application::class);
        $this->jobRepository = $entityManager->getRepository(JobOffer::class);
    }


    #[Route('/recruiter/count-application-status', name: 'app_count_application_status', methods: ['GET'])]
    public function countStatusApplication(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur connecté', 404);
        }

        $job = $this->jobRepository->findBy(['user' => $user->getId()]);
        if (!$job) {
            return $this->json('Aucune correspondance de l\'offfre');
        }

        $count_pending = count(($this->applicationRepository)->findBy(['application_status' => 'en attente', 'job' => $job]));

        $count_received = count(($this->applicationRepository)->findBy(['application_status' => 'reçu', 'job' => $job]));

        $count_confirmed = count(($this->applicationRepository)->findBy(['application_status' => 'confirmée', 'job' => $job]));

        $count_refused = count(($this->applicationRepository)->findBy(['application_status' => 'refusée', 'job' => $job]));

        $data = [
            'countPending' => $count_pending,
            'countReceived' => $count_received,
            'countConfirmed' => $count_confirmed,
            'countRefused' => $count_refused
        ];

        return $this->json($data);
    }
}
