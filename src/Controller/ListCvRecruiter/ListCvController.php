<?php

namespace App\Controller\ListCvRecruiter;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_RECRUITER')]
class ListCvController extends AbstractController
{

    private $entityManager;
    private $cvRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cvRepository = $entityManager->getRepository(File::class);
    }

    #[Route('/api/get-all-cv', name: 'get_all_list_cv', methods: ['GET'])]
    public function getAllList(): JsonResponse
    {
        $cvs = $this->cvRepository->findBy(['isActive' => true]);
        if (!$cvs) {
            return $this->json('Aucun cv activé');
        }
        $data = [];

        foreach ($cvs as $cv) {
            $safeCvName = $this->getSafeCvName($cv->getResume());
            $data[] = [
                'id' => $cv->getId(),
                'resume' => $safeCvName,
            ];
        }
        return new JsonResponse($data);
    }

    private function getSafeCvName(string $filename): string
    {
        $pattern = '/-\w+(?=\.[^.]+$)/';
        $cleanedFilename = preg_replace($pattern, '', $filename);

        return $cleanedFilename;
    }
}