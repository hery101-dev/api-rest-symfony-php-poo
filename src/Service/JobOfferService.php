<?php

namespace App\Service;

use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class JobOfferService
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function getAllJobOffersData(): string
    {
        $jobOffers = $this->entityManager->getRepository(JobOffer::class)->findAll();

        usort($jobOffers, function ($a, $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        $jobOffersData = [];
        foreach ($jobOffers as $jobOffer) {
            $jobOffersData[] = $this->transformJobOfferToArray($jobOffer);
        }

        return $this->serializer->serialize($jobOffersData, 'json');
    }

    public function getJobOfferById(int $jobOfferId): array
    {
        $jobOffer = $this->entityManager->getRepository(JobOffer::class)->find($jobOfferId);

        if ($jobOffer) {
            $jobOfferData = $this->transformJobOfferToArray($jobOffer);
            return $jobOfferData;
        }

        return [];
    }

    private function transformJobOfferToArray(JobOffer $jobOffer): array
    {
        $jobData = [
            
        ];

        return $jobData;
    }
}