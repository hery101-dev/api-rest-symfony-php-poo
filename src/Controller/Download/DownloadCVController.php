<?php

namespace App\Controller\Download;


use App\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;



class DownloadCVController extends AbstractController
{
    private $entityManager;
    private $cvRepository;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cvRepository = $entityManager->getRepository(File::class);
    }


    #[Route('/api/download-cv-candidate/{id}', name: 'app_download_resume_recruiter', methods: ['GET'])]
    public function getDownloadCV(int $id)
    {
        $getFile =  $this->cvRepository->find($id);

        if (!$getFile) {
            return $this->json('Fichier introuvable', 404);
        }

        $nameResume = basename($getFile->getResume());
        $filePath = $this->getParameter('file_directory') . '/' . $nameResume;

        if (!file_exists($filePath)) {
            return $this->json('Fichier non trouvé sur le serveur', 404);
        }

        // Utiliser pathinfo pour obtenir l'extension originale du fichier
        $fileExtension = pathinfo($nameResume, PATHINFO_EXTENSION);
        $filenameParts = explode('-', $nameResume);
        array_pop($filenameParts); // Enlever l'identifiant unique
        $cleanFilename = implode('-', $filenameParts);

        $response = new BinaryFileResponse($filePath);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $cleanFilename . '.' . $fileExtension // Utiliser l'extension originale
        );

        return $response;
    }
}
