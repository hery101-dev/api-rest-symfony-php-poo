<?php

namespace App\Controller\Recruiter;

use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_RECRUITER')]
class StatusJobController extends AbstractController
{
    #[Route('/api/recruiter/status-job/{id}', name: 'toggle_status', methods: ['PUT'])]
    public function toggleJobStatus(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $jobOffer = $entityManager->getRepository(JobOffer::class)->find($id);
        if (!$jobOffer) {
            return new JsonResponse('Offre introuvable', 404);
        }
        $currentStatus = $jobOffer->isJobStatus();

        $jobOffer->setJobStatus(!$currentStatus);

        $entityManager->persist($jobOffer);
        $entityManager->flush();

        return new JsonResponse(['status' => true], 200);
    }
}
