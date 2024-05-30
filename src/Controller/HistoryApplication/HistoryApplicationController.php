<?php

namespace App\Controller\HistoryApplication;


use App\Entity\File;
use App\Entity\Partner;
use App\Entity\JobOffer;
use App\Entity\Application;
use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HistoryApplicationController extends AbstractController
{
    private $entityManager;
    private $applicationRepository;
    private $applicationJobOffer;
    private $userRepository;
    private $companyRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->applicationRepository = $entityManager->getRepository(Application::class);
        $this->applicationJobOffer = $entityManager->getRepository(JobOffer::class);
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->companyRepository = $entityManager->getRepository(Company::class);
    }

    private function getSafeCvName(string $filename): string
    {
        $pattern = '/-\w+(?=\.[^.]+$)/';
        $cleanedFilename = preg_replace($pattern, '', $filename);

        return $cleanedFilename;
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/api/recruiter/application-recruiter-list', name: 'application_history_recruiter', methods: ['GET'])]
    public function getApplicationRecruiter(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }

        $job = $this->applicationJobOffer->findBy(['user' => $user]);
        if (!$job) {
            return $this->json('Offre introuvable');
        }

        $applicationJobs = $this->applicationRepository->findBy(['job' => $job]);
        if (!$applicationJobs) {
            return $this->json('Candidature introuvable');
        }


        $data = [];
        foreach ($applicationJobs as $application) {
            $jobApp = $application->getJob();

            $candidate = $application->getCandidate();
            $resume = $this->getSafeCvName($application->getApplicationResume());
            $coverLetter = $this->getSafeCvName($application->getCoverLetter());

            $data[] = [
                'id' => $application->getId(),
                'resume' =>  $resume,
                'submitedAt' => $application->getSubmitedAt(),
                'coverLetter' => $coverLetter,
                'candidate' => $candidate ? $candidate->getEmail() : null,
                'job' => $jobApp ? $jobApp->getTitle() : null,
                'status' => $application->getApplicationStatus(),
            ];
        }
        return new JsonResponse($data);
    }






    #[IsGranted('ROLE_CANDIDATE')]
    #[Route('/api/candidate/application-candidate-list', name: 'application_history_candidate', methods: ['GET'])]
    public function getApplicationCandidate(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }
        $applications = $this->applicationRepository->findBy(['candidate' => $user]);

        if (!$applications) {
            return $this->json('Candidature introuvable');
        }

        $data = [];
        foreach ($applications as $application) {
            $job = $application->getJob();
            $getCompany = $job->getCompany();
            $company = $this->companyRepository->findOneBy(['id' => $getCompany]);
            $companyName = $company ? $company->getCompanyName() : 'Entreprise introuvable';
            $userIdJob =  $job ? $job->getUserId() : 'Utilisateur introuvable depuis les offres';
            $user = $this->userRepository->findOneBy(['id' => $userIdJob]);
            $email = $user ? $user->getEmail() : 'E-mail du recruteur introuvable';

            $data[] = [
                'id' => $application->getId(),
                'submitedAt' => $application->getSubmitedAt(),
                'applicationStatus' => $application->getApplicationStatus(),
                'job' => $job ? $job->getTitle() : null,
                'emailRecruiter' => $email,
                'company' => $companyName
            ];
        }
        return new JsonResponse($data);
    }



    #[IsGranted('ROLE_CANDIDATE')]
    #[Route('/api/candidate/application-candidate-detail-history/{id}', name: 'application__detail_history_candidate', methods: ['GET'])]
    public function getApplicationDetail(int $id): JsonResponse
    {

        $applications = $this->applicationRepository->find($id);

        if (!$applications) {
            return $this->json('Candidature introuvable');
        }

        $job = $applications->getJob();
        $getCompany = $job->getCompany();
        $message = $applications->getMessage();
        //$contrat = $job->getContrat();
        $company = $this->companyRepository->findOneBy(['id' => $getCompany]);
        $companyName = $company ? $company->getCompanyName() : 'Entreprise introuvable';
        $userIdJob =  $job ? $job->getUserId() : 'Utilisateur introuvable depuis les offres';
        $user = $this->userRepository->findOneBy(['id' => $userIdJob]);
        $email = $user ? $user->getEmail() : 'E-mail du recruteur introuvable';

        $data[] = [
            'id' => $applications->getId(),
            'submitedAt' => $applications->getSubmitedAt(),
            'applicationStatus' => $applications->getApplicationStatus(),
            'job' => $job ? $job->getTitle() : null,
            'emailRecruiter' => $email,
            'company' => $companyName,
            'message' => $message,
            //'contrat' => $contrat
        ];

        return new JsonResponse($data);
    }
}
