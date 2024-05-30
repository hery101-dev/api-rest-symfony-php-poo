<?php

namespace App\Controller\Candidate;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_CANDIDATE')]
class CandidateListCVController extends AbstractController
{
    private $entityManager;
    private $cvRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cvRepository = $entityManager->getRepository(File::class);
    }

    #[Route('/list-cv', name: 'list_cv_candidate', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }
        $cvs = $this->cvRepository->findOneBy(['user' => $user]);
        if (!$cvs) {
            return $this->json('Aucun cv trouvé');
        }

        $safeCvName = $this->getSafeCvName($cvs->getResume());
        $data = [
            'id' => $cvs->getId() ? $cvs->getId() : null,
            'resume' => $safeCvName ? $safeCvName : null,
            'isActive' => $cvs->isIsActive(),
        ];

        return $this->json($data);
    }

    private function getSafeCvName(string $filename): string
    {
        $pattern = '/-\w+(?=\.[^.]+$)/';
        $cleanedFilename = preg_replace($pattern, '', $filename);

        return $cleanedFilename;
    }



    #[Route('/{id}/toggleActive', name: 'cv_toggle_active', methods: ['PUT'])]
    public function toggleActive(File $cv): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('No user does exist', 404);
        }

        if ($user->getId() !== $cv->getUser()->getId()) {
            return $this->json(['status' => 'error', 'message' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($cv->isIsActive()) {
            $cv->setIsActive(false);
        } else {
            // $activeCVs = $this->cvRepository->findBy(['user' => $user, 'isActive' => true]);

            // foreach ($activeCVs as $activeCv) {
            //     $activeCv->setIsActive(false);
            // }

            $cv->setIsActive(true);
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'success', 'isActive' => $cv->isIsActive()]);
    }



    #[Route('/{id}/remove-cv', name: 'cv_delete', methods: ['DELETE'])]
    public function delete(File $cv): JsonResponse
    {
        $this->entityManager->remove($cv);
        $this->entityManager->flush();

        return $this->json(['status' => 'success', 'message' => 'CV supprimé avec succès']);
    }
}
