<?php

namespace App\Controller\Candidate;


use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_CANDIDATE')]
class UploadCVController extends AbstractController 
{
    private $entityManager;
    private $fileRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->fileRepository = $entityManager->getRepository(File::class);
    }

    #[Route('/api/candidate/upload-resume', name: 'upload_resume_candidate', methods: ['POST'])]
    public function uploadResume(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Aucun utilisateur connecté', 404);
        }

        $resume = $request->files->get('resume');

        if (!$resume) {
            return $this->json('Aucun fichier séléctionner');
        }

        $alreadyUploadResume = $this->fileRepository->findOneBy(['user' => $userActuel]);

        if ($alreadyUploadResume) {
            return new JsonResponse('Le CV existe déjà, vous devez donc supprimer votre CV actuel, merci');
        }

        if ($resume) {
            $upload = new File;
            $newFileCV = $this->uploadFile($resume, $slugger, $userActuel);
            $upload->setResume($newFileCV);
            $upload->setUser($userActuel);
            $upload->setIsActive(false);
        } else {
            return new JsonResponse(['error' => 'Aucun cv fourni'], Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->persist($upload);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => true,
        ], 200);
    }

    private function uploadFile($file, $slugger, $user): string
    {
        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFileName = $slugger->slug($originalFileName);
        $newFileName = $safeFileName . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->getParameter('file_directory'), $newFileName);

        return $newFileName;
    }
}