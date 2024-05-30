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
class RefusedAfterInterviewController extends AbstractController
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

    #[Route('/recruiter/refused-after-interview/{id}', name: 'app_refused_after_interview_recruiter', methods: ['PUT'])]
    public function refusedAfterInterview(int $id): JsonResponse
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

        if ($app_status->getApplicationStatus() === 'confirmée') {

            $app_status->setApplicationStatus('refusée');
            $app_status->setMessage("
            Bonjour,
            \nJe tiens à vous remercier personnellement pour le temps que vous avez consacré à l'entretien pour le poste de 
            \n$title et pour l'intérêt que vous avez montré envers $company.
            \nAprès un examen attentif et des délibérations, nous avons décidé de ne pas poursuivre votre candidature pour ce poste. 
            \nCette décision n'a pas été facile compte tenu de vos qualités évidentes.
            \nNous espérons que vous ne découragerez pas et que vous continuerez à envisager $company pour des opportunités futures.

            \nNous vous souhaitons tout le succès dans votre recherche d'emploi et dans votre parcours professionnel.

            \nCordialement,
            \n$company
            
            ");
        }

        $this->entityManager->flush();

        if ($app_status->getApplicationStatus() === 'refusée') {
            $email = (new Email())
                ->from($user->getUserIdentifier())
                ->to($app_status->getCandidate()->getEmail())
                ->subject("Retour suite à votre entretien pour $title chez $company")
                ->text($app_status->getMessage());
            $this->mailer->send($email);
        }
        return $this->json(['message' => 'l\'état du candidature a changé avec succès']);
    }
}
