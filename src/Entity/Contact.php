<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname_contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname_contact = null;

    #[ORM\Column(length: 255)]
    private ?string $email_contact = null;

    #[ORM\Column(length: 255)]
    private ?string $object_msg = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content_msg = null;

    #[ORM\Column]
    private ?bool $existing_account = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstnameContact(): ?string
    {
        return $this->firstname_contact;
    }

    public function setFirstnameContact(?string $firstname_contact): static
    {
        $this->firstname_contact = $firstname_contact;

        return $this;
    }

    public function getLastnameContact(): ?string
    {
        return $this->lastname_contact;
    }

    public function setLastnameContact(?string $lastname_contact): static
    {
        $this->lastname_contact = $lastname_contact;

        return $this;
    }

    public function getEmailContact(): ?string
    {
        return $this->email_contact;
    }

    public function setEmailContact(string $email_contact): static
    {
        $this->email_contact = $email_contact;

        return $this;
    }

    public function getObjectMsg(): ?string
    {
        return $this->object_msg;
    }

    public function setObjectMsg(string $object_msg): static
    {
        $this->object_msg = $object_msg;

        return $this;
    }

    public function getContentMsg(): ?string
    {
        return $this->content_msg;
    }

    public function setContentMsg(string $content_msg): static
    {
        $this->content_msg = $content_msg;

        return $this;
    }

    public function isExistingAccount(): ?bool
    {
        return $this->existing_account;
    }

    public function setExistingAccount(bool $existing_account): static
    {
        $this->existing_account = $existing_account;

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
