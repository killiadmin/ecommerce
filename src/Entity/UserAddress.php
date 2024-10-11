<?php

namespace App\Entity;

use App\Repository\UserAddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAddressRepository::class)]
class UserAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userAddresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_associated = null;

    #[ORM\Column(nullable: true)]
    private ?int $number_delivery = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle_delivery = null;

    #[ORM\Column(nullable: true)]
    private ?int $code_delivery = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city_delivery = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $additionnal_information = null;

    #[ORM\Column]
    private ?bool $billing = null;

    #[ORM\Column(nullable: true)]
    private ?int $number_billing = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle_billing = null;

    #[ORM\Column(nullable: true)]
    private ?int $code_billing = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city_billing = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserAssociated(): ?User
    {
        return $this->user_associated;
    }

    public function setUserAssociated(?User $user_associated): static
    {
        $this->user_associated = $user_associated;

        return $this;
    }

    public function getNumberDelivery(): ?int
    {
        return $this->number_delivery;
    }

    public function setNumberDelivery(?int $number_delivery): static
    {
        $this->number_delivery = $number_delivery;

        return $this;
    }

    public function getLibelleDelivery(): ?string
    {
        return $this->libelle_delivery;
    }

    public function setLibelleDelivery(?string $libelle_delivery): static
    {
        $this->libelle_delivery = $libelle_delivery;

        return $this;
    }

    public function getCodeDelivery(): ?int
    {
        return $this->code_delivery;
    }

    public function setCodeDelivery(?int $code_delivery): static
    {
        $this->code_delivery = $code_delivery;

        return $this;
    }

    public function getCityDelivery(): ?string
    {
        return $this->city_delivery;
    }

    public function setCityDelivery(?string $city_delivery): static
    {
        $this->city_delivery = $city_delivery;

        return $this;
    }

    public function getAdditionnalInformation(): ?string
    {
        return $this->additionnal_information;
    }

    public function setAdditionnalInformation(?string $additionnal_information): static
    {
        $this->additionnal_information = $additionnal_information;

        return $this;
    }

    public function isBilling(): ?bool
    {
        return $this->billing;
    }

    public function setBilling(bool $billing): static
    {
        $this->billing = $billing;

        return $this;
    }

    public function getNumberBilling(): ?int
    {
        return $this->number_billing;
    }

    public function setNumberBilling(?int $number_billing): static
    {
        $this->number_billing = $number_billing;

        return $this;
    }

    public function getLibelleBilling(): ?string
    {
        return $this->libelle_billing;
    }

    public function setLibelleBilling(?string $libelle_billing): static
    {
        $this->libelle_billing = $libelle_billing;

        return $this;
    }

    public function getCodeBilling(): ?int
    {
        return $this->code_billing;
    }

    public function setCodeBilling(?int $code_billing): static
    {
        $this->code_billing = $code_billing;

        return $this;
    }

    public function getCityBilling(): ?string
    {
        return $this->city_billing;
    }

    public function setCityBilling(?string $city_billing): static
    {
        $this->city_billing = $city_billing;

        return $this;
    }
}
