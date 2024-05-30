<?php

namespace App\Controller\SystemFollow;


use App\Entity\JobOffer;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api')]
#[IsGranted('ROLE_RECRUITER')]
class MessageReçuController extends AbstractController
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

    #[Route('/recruiter/message-reçu/{id}', name: 'app_message_reçu_recruiter', methods: ['PUT'])]
    public function reçu(Request $request, int $id): JsonResponse
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

        $content['message'] = json_decode($request->getContent(), true);
        $userMessage = isset($content['message']) ? json_encode($content['message']) : null;

        $title = strtoupper($job->getTitle());
        $company = strtoupper($job->getCompany()->getCompanyName());

        if ($app_status->getApplicationStatus() === 'en attente') {

            $app_status->setApplicationStatus('reçu');
            $app_status->setMessage($userMessage);
        }

        $this->entityManager->flush();

        if ($app_status->getApplicationStatus() === 'reçu') {
            $email = (new Email())
                ->from($user->getUserIdentifier())
                ->to($app_status->getCandidate()->getEmail())
                ->subject("Candidature enregistrée pour $title - $company")
                ->text($app_status->getMessage());
            $this->mailer->send($email);
        }
        return $this->json(['message' => 'l\'état du candidature a changé avec succès']);
    }
}
