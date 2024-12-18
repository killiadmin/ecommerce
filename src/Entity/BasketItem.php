<?php

namespace App\Entity;

use App\Repository\BasketItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketItemRepository::class)]
class BasketItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    private ?Basket $basket = null;

    #[ORM\ManyToOne(inversedBy: 'basketItems')]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: 'float', precision: 10, scale: 2)]
    private ?float $price = null;

    #[ORM\Column(type: 'float', precision: 10, scale: 2)]
    private ?float $price_tva = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(?Basket $basket): static
    {
        $this->basket = $basket;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return (int) $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = round($price,2);

        return $this;
    }

    public function getPriceTva(): ?float
    {
        return $this->price_tva;
    }

    public function setPriceTva(float $price_tva): static
    {
        $this->price_tva = round($price_tva, 2);

        return $this;
    }
}
