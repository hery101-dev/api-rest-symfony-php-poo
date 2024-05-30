<?php

namespace App\Controller\Application;

use App\Entity\JobOffer;
use App\Entity\Application;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Mime\Email;



#[IsGranted('ROLE_CANDIDATE')]
class ApplicationController extends AbstractController
{

    #[Route('/api/candidate/application-upload-file', name: 'upload_file_candidate', methods: ['POST'])]
    public function uploadFileCandidate(
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger,
        MailerInterface $mailer,
    ): JsonResponse {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Aucun utilisateur existant', 404);
        }

        $resume = $request->files->get('resume');
        $coverLetterFile = $request->files->get('coverLetterFile');
        $jobOfferId = $request->request->get('id');

        $job = $entityManager->getRepository(JobOffer::class)->find($jobOfferId);
        if (!$job) {
            return $this->json('Aucune correspondance de l\'offre');
        }
        $categoryJob = $job->getCategory()->getCategoryName();

        $exist = $entityManager->getRepository(Application::class)->findOneBy(['job' => $job, 'candidate' => $userActuel]);
        if ($exist) {
            return new JsonResponse('Une candidature existe déjà pour ce poste');
        }

        $apply = new Application();
        $apply->setApplicationStatus('en attente');
        $apply->setCandidate($userActuel);
        $apply->setJob($job);
        $apply->setCategoryJob($categoryJob);

        if ($resume) {
            $newFileCV = $this->uploadFile($resume, $slugger, $userActuel);
            $apply->setApplicationResume($newFileCV);
            $entityManager->persist($apply);
        } else {
            return new JsonResponse(['error' => 'Aucun CV fourni'], Response::HTTP_BAD_REQUEST);
        }

        if ($coverLetterFile) {
            $newFileLM = $this->uploadFile($coverLetterFile, $slugger, $userActuel);
            $apply->setCoverLetter($newFileLM);
            $entityManager->persist($apply);
        } else {
            return new JsonResponse(['error' => 'Aucun fichier de lettre de motivation téléchargé'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        //$recruiterEmail = $job->getUserId();
        $userRecruiter = $entityManager->getRepository(User::class)->findOneBy(['id' => $job->getUserId()]);
        $userRecruiter ? $userRecruiter->getEmail() : 'Recruteur introuvable';
        $candidateEmail = $userActuel->getUserIdentifier(); 
        $host = ' <a href="http://localhost:5173/recruiter/dashboard/list-job">ici</a>';
       
        $email = (new Email())
        ->from($candidateEmail)
        ->to($userRecruiter->getEmail())
        ->subject('Nouvelle demande de candidature pour' . $job->getTitle())
        ->text("Une nouvelle candidature a été déposée. Veuillez consulter le site pour voir la candidature " .$host);
        
        $mailer->send($email);

        return new JsonResponse('Fichiers téléchargés avec succès', Response::HTTP_OK);
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
