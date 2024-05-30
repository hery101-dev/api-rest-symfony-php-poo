<?php

namespace App\Entity;

use App\Repository\RecommandationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecommandationRepository::class)]
class Recommandation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $postulant = null;

    #[ORM\ManyToOne]
    private ?JobOffer $recommend_job = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostulant(): ?User
    {
        return $this->postulant;
    }

    public function setPostulant(?User $postulant): static
    {
        $this->postulant = $postulant;

        return $this;
    }

    public function getRecommendJob(): ?JobOffer
    {
        return $this->recommend_job;
    }

    public function setRecommendJob(?JobOffer $recommend_job): static
    {
        $this->recommend_job = $recommend_job;

        return $this;
    }
}
