<?php

namespace App\Entity;

use App\Repository\DiscountCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscountCodeRepository::class)]
class DiscountCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name_code = null;

    #[ORM\Column]
    private ?float $reduction = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(nullable: true)]
    private ?int $nb_usage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameCode(): ?string
    {
        return $this->name_code;
    }

    public function setNameCode(string $name_code): static
    {
        $this->name_code = $name_code;

        return $this;
    }

    public function getReduction(): ?float
    {
        return $this->reduction;
    }

    public function setReduction(float $reduction): static
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getNbUsage(): ?int
    {
        return $this->nb_usage;
    }

    public function setNbUsage(?int $nb_usage): static
    {
        $this->nb_usage = $nb_usage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
