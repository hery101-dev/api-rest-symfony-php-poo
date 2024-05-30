<?php

namespace App\Entity;

use App\Repository\OfferViewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferViewRepository::class)]
class OfferView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], targetEntity: JobOffer::class)]
    private ?JobOffer $jobOffer = null;

    #[ORM\ManyToOne(cascade: ['persist'], targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $view_count = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $viewedAt = null;


    
    public function __construct()
    {
        $this->viewedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): static
    {
        $this->jobOffer = $jobOffer;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getViewCount(): ?int
    {
        return $this->view_count;
    }

    public function setViewCount(?int $view_count): static
    {
        $this->view_count = $view_count;

        return $this;
    }

    public function getViewedAt(): ?\DateTimeInterface
    {
        return $this->viewedAt;
    }

    public function setViewedAt(?\DateTimeInterface $viewedAt): static
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }

    public function incrementViewCount(): self
    {
        $this->view_count++;

        return $this;
    }
}
