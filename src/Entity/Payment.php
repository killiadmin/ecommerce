<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_payment = null;

    #[ORM\Column(length: 255)]
    private ?string $number_payment = null;

    #[ORM\Column(length: 255)]
    private ?string $masked_number_payment = null;

    #[ORM\Column(length: 255)]
    private ?string $type_payment = null;

    #[ORM\Column(type: Types::STRING, length: 5)]
    private ?string $expiration_date_payment = null;

    #[ORM\Column]
    private ?bool $active_payment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname_payment = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname_payment = null;

    #[ORM\Column]
    private ?bool $select_payment = null;

    public function __construct()
    {
        $this->type_payment = 'cb';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserPayment(): ?User
    {
        return $this->user_payment;
    }

    public function setUserPayment(?User $user_payment): static
    {
        $this->user_payment = $user_payment;

        return $this;
    }

    public function getNumberPayment(): ?string
    {
        return $this->number_payment;
    }

    public function setNumberPayment(string $number_payment): static
    {
        $this->number_payment = $number_payment;

        return $this;
    }

    public function getMaskedNumberPayment(): ?string
    {
        return $this->masked_number_payment;
    }

    public function setMaskedNumberPayment(string $masked_number_payment): static
    {
        $this->masked_number_payment = $masked_number_payment;

        return $this;
    }

    public function getTypePayment(): ?string
    {
        $this->type_payment === 'CB';

        return $this->type_payment;
    }

    public function setTypePayment(string $type_payment): static
    {
        $this->type_payment = $type_payment;

        return $this;
    }

    public function getExpirationDatePayment(): ?string
    {
        return $this->expiration_date_payment;
    }

    public function setExpirationDatePayment(string $expiration_date_payment): static
    {
        $this->expiration_date_payment = $expiration_date_payment;

        return $this;
    }

    public function isActivePayment(): ?bool
    {
        return $this->active_payment;
    }

    public function setActivePayment(bool $active_payment): static
    {
        $this->active_payment = $active_payment;

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

    public function getFirstnamePayment(): ?string
    {
        return $this->firstname_payment;
    }

    public function setFirstnamePayment(string $firstname_payment): static
    {
        $this->firstname_payment = $firstname_payment;

        return $this;
    }

    public function getLastnamePayment(): ?string
    {
        return $this->lastname_payment;
    }

    public function setLastnamePayment(string $lastname_payment): static
    {
        $this->lastname_payment = $lastname_payment;

        return $this;
    }

    public function isSelectPayment(): ?bool
    {
        return $this->select_payment;
    }

    public function setSelectPayment(bool $select_payment): static
    {
        $this->select_payment = $select_payment;

        return $this;
    }
}
