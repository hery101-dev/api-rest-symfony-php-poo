<?php

namespace App\Controller\Download;


use ZipArchive;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class DownloadApplicationController extends AbstractController
{

    private $entityManager;
    private  $application;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->application = $entityManager->getRepository(Application::class);
    }



    #[Route('/download-files/{id}', name: 'api_download_files', methods: ['GET'])]
    public function getDownloadApplication(int $id)
    {
        $getFile =  $this->application->find($id);

        if (!$getFile) {
            return $this->json('Fichiers introuvable', 404);
        }
        $nameResume = basename($getFile->getApplicationResume());
        $nameCoverLetter = basename($getFile->getCoverLetter());

        $filePathCv = $this->getParameter('file_directory') . '/' . basename($nameResume);
        $filePathCoverLetter = realpath($this->getParameter('file_directory') . '/' . basename($nameCoverLetter));


        $zipFile = new ZipArchive();
        $tempFileName = tempnam(sys_get_temp_dir(), 'downloaded_files_');
        $zipFile->open('downloaded_files.zip', \ZipArchive::CREATE);

        $zipFile->addFile($filePathCv);
        $zipFile->addFile($filePathCoverLetter);

        $response = new BinaryFileResponse($tempFileName);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'downloaded_files.zip'
        );

        
        return $response;
    }
}
