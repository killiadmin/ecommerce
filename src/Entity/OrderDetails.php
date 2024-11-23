<?php

namespace App\Entity;

use App\Repository\OrderDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderDetailsRepository::class)]
class OrderDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    private ?Order $order_associated = null;

    #[ORM\ManyToOne(inversedBy: 'created_at')]
    private ?Product $product_associated = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderAssociated(): ?Order
    {
        return $this->order_associated;
    }

    public function setOrderAssociated(?Order $order_associated): static
    {
        $this->order_associated = $order_associated;

        return $this;
    }

    public function getProductAssociated(): ?Product
    {
        return $this->product_associated;
    }

    public function setProductAssociated(?Product $product_associated): static
    {
        $this->product_associated = $product_associated;

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
