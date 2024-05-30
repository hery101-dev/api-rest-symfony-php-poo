<?php

namespace App\Controller\SystemFollow;


use App\Entity\JobOffer;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api')]
#[IsGranted('ROLE_RECRUITER')]
class RefusedController extends AbstractController
{
    private $entityManager;
    private $applicationRepository;
    private $jobRepository;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $email)
    {
        $this->entityManager = $entityManager;
        $this->applicationRepository = $entityManager->getRepository(Application::class);
        $this->jobRepository = $entityManager->getRepository(JobOffer::class);
        $this->mailer = $email;
    }

    #[Route('/recruiter/refused/{id}', name: 'app_refused_recruiter', methods: ['PUT'])]
    public function refused(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur connecté');
        }

        $app_status = $this->applicationRepository->find($id);
        if (!$app_status) {
            return $this->json('Candidature introuvable');
        }

        $job = $this->jobRepository->findOneBy(['id' => $app_status->getJob()]);
        if (!$job) {
            return $this->json('Aucune correspondance de l\'offre');
        }
        $title = strtoupper($job->getTitle());
        $company = strtoupper($job->getCompany()->getCompanyName());

        if ($app_status->getApplicationStatus() === 'reçu') {

            $app_status->setApplicationStatus('refusée');
            $app_status->setMessage("
                Bonjour,
                \nNous vous remercions d'avoir postulé pour le poste de $title chez $company. 
                \nAprès un examen attentif de votre candidature, nous avons décidé de poursuivre avec d'autres candidats 
                \ndont les compétences et l'expérience correspondent davantage aux besoins de ce poste.
                
                \nNous vous encourageons à continuer de suivre nos offres d'emploi futures et à postuler pour celles qui correspondent à votre profil.
                
                \nNous vous souhaitons le meilleur dans votre recherche d'emploi.
                
                \nCordialement,
                \n$company            
            ");
        }

        $this->entityManager->flush();

        if ($app_status->getApplicationStatus() === 'refusée') {
            $email = (new Email())
                ->from($user->getUserIdentifier())
                ->to($app_status->getCandidate()->getEmail())
                ->subject("Notification de résultat de candidature de $title chez $company")
                ->text($app_status->getMessage());
            $this->mailer->send($email);
        }
        return $this->json(['message' => 'l\'état du candidature a changé avec succès']);
    }
}
