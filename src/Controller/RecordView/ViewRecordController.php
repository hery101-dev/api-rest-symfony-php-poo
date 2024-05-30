<?php

// src/Controller/ViewRecordController.php

namespace App\Controller\RecordView;

use App\Entity\User;
use App\Entity\JobOffer;
use App\Entity\OfferView;
use App\Entity\Recommandation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api')]
#[IsGranted('ROLE_CANDIDATE')]
class ViewRecordController extends AbstractController
{

    #[Route('/record-view', name: 'record_view', methods: ['POST'])]
    public function recordView(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur trouvé', Response::HTTP_NOT_FOUND);
        }
        $userIdentify = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$userIdentify) {
            return $this->json('Aucun correspondane de l\'utilisateur ');
        }
        $userType = $userIdentify->getUserType();
        if ($userType !== 'candidate') {
            return $this->json('Le type d(\'utilisateur n\'est pas autorisé');
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['jobId'])) {
            return $this->json('Aucune offre trouvée', Response::HTTP_BAD_REQUEST);
        }

        $offerId = $data['jobId'];
        $jobOffer = $entityManager->getRepository(JobOffer::class)->find($offerId);
        if (!$jobOffer) {
            return $this->json(['message' => 'Offre introuvable'], Response::HTTP_NOT_FOUND);
        }


        $offerView = $entityManager->getRepository(OfferView::class)->findOneBy([
            'jobOffer' => $jobOffer,
            'user' => $user
        ]);

        if ($offerView) {
            $offerView->incrementViewCount();
        } else {
            $offerView = new OfferView();
            $offerView->setJobOffer($jobOffer);
            $offerView->setUser($user);
            $offerView->setViewCount(1);

            $entityManager->persist($offerView);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Vue enregistrée',
        ]);
    }

    #[Route('/job-offer-recommend', name: 'job_offer_recommend_by_ia', methods: ['GET'])]
    public function recommandationSystem(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }

        $id = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$id) {
            return $this->json('Utilisateur introuvable');
        }

        $recommendations = $entityManager->getRepository(Recommandation::class)->findBy([
            'postulant' => $user
        ]);

        if (!$recommendations) {
            return $this->json('Aucune recommandation pour un utilisateur connecté');
        }

        $recommendedJobsData = [];
        foreach ($recommendations as $recommendation) {
            $job = $recommendation->getRecommendJob();
            $company = $job->getCompany()->getCompanyName();
            $JobsData = [
                'id' => $job->getId(),
                'title' => $job->getTitle(),
                'description' => $job->getDescription(),
                'company' => $company ? $company : null,
                'createdAt' => $job->getCreatedAt(),
                'deadline' => $job->getDeadlineAt() ? $job->getDeadlineAt() : null,
            ];

            if ($job->isJobStatus() == true) {
                $recommendedJobsData[] = $JobsData;
            }
        }

        return $this->json($recommendedJobsData);
    }

    // private function callRecommendationScript($userId)
    // {
    //     // Chemin vers votre script Python
    //     $scriptPath = 'C:\Users\TL\Desktop\I.A\recommandation_NMF1.py';

    //     // Création et exécution du processus
    //     $process = new Process(['python', $scriptPath, $userId]);
    //     $process->run();

    //     // Vérification s'il y a eu une erreur lors de l'exécution
    //     if (!$process->isSuccessful()) {
    //         throw new ProcessFailedException($process);
    //     }

    //     // Récupération de la sortie du script
    //     return $process->getOutput();
    // }
}
