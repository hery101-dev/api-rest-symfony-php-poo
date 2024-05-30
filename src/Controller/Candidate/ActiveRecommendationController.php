<?php

namespace App\Controller\Candidate;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_CANDIDATE')]
class ActiveRecommendationController extends AbstractController
{
    #[Route('/api/candidate/status-recommendation/{id}', name: 'toggle_recommendation_status', methods: ['PUT'])]
    public function toggleRecommendationStatus(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse('Aucun utilisateur trouvé pour la recommandation');
        }
        $currentStatus = $user->isRecommendationsEnabled();

        $user->setRecommendationsEnabled(!$currentStatus);

        $entityManager->flush();

        return new JsonResponse(['enabled' => $user->isRecommendationsEnabled()]);
    }
}
