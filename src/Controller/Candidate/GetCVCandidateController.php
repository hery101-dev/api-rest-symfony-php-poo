<?php

namespace App\Controller\Candidate;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CANDIDATE')]
class GetCVCandidateController extends AbstractController
{
    #[Route('/api/candidate/get-cv', name: 'get_cv_candidate', methods: ['GET'])]
    public function getExistingCVs(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }

        $existingCVs = $entityManager->getRepository(File::class)->findOneBy(['user' => $user]);
        if (!$existingCVs) {
            return $this->json('Aucun cv trouvé');
        }

        foreach ($existingCVs as $cv) {
            $formattedCVs[] = [
                'id' => $cv->getId(),
                'resume' => $cv->getResume(),
            ];
        }

        return $this->json($formattedCVs);
    }
}
