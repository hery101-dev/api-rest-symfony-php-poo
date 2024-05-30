<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne( cascade: ['persist'], targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'candidate_id', referencedColumnName: 'id', nullable: true)]
    private ?User $candidate = null;

    #[ORM\ManyToOne(cascade: ['persist'], targetEntity: JobOffer::class)]
    #[ORM\JoinColumn(name: 'job_id', referencedColumnName: 'id', nullable: true)]
    private ?JobOffer $job = null;

    #[ORM\Column(length: 255)]
    private ?string $application_status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover_letter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $other_file = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $submitedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $application_resume = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categoryJob = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    public function __construct()
    {
        $this->submitedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): ?User
    {
        return $this->candidate;
    }

    public function setCandidate(User $candidate): static
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getJob(): ?JobOffer
    {
        return $this->job;
    }

    public function setJob(JobOffer $job): static
    {
        $this->job = $job;

        return $this;
    }

    
    public function getCoverLetter(): ?string
    {
        return $this->cover_letter;
    }

    public function setCoverLetter(?string $cover_letter): static
    {
        $this->cover_letter = $cover_letter;

        return $this;
    }

    public function getOtherFile(): ?string
    {
        return $this->other_file;
    }

    public function setOtherFile(?string $other_file): static
    {
        $this->other_file = $other_file;

        return $this;
    }

    public function getApplicationStatus(): ?string
    {
        return $this->application_status;
    }

    public function setApplicationStatus(string $application_status): static
    {
        $this->application_status = $application_status;

        return $this;
    }

    public function getSubmitedAt(): ?\DateTimeImmutable
    {
        return $this->submitedAt;
    }

    public function setSubmitedAt(\DateTimeImmutable $submitedAt): static
    {
        $this->submitedAt = $submitedAt;

        return $this;
    }

    public function getApplicationResume(): ?string
    {
        return $this->application_resume;
    }

    public function setApplicationResume(?string $application_resume): static
    {
        $this->application_resume = $application_resume;

        return $this;
    }

    public function getCategoryJob(): ?string
    {
        return $this->categoryJob;
    }

    public function setCategoryJob(?string $categoryJob): static
    {
        $this->categoryJob = $categoryJob;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
