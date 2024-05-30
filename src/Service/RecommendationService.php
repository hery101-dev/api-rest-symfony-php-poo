<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ApplicationRepository;

class RecommendationService
{
    private $applicationRepository;

    public function __construct(ApplicationRepository $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    public function proposeJobOffersByCategory(User $candidate, $categoryId)
    {
        $numberOfApplications = $this->applicationRepository->countApplicationsByCategory($candidate, $categoryId);

        if ($numberOfApplications > 1) {
            return $this->applicationRepository->findJobOffersByCategoryExcludingCandidate($categoryId, $candidate);
        }

        return [];
    }
}