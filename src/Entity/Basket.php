<?php

namespace App\Entity;

use App\Repository\BasketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
class Basket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'basket', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, BasketItem>
     */
    #[ORM\OneToMany(targetEntity: BasketItem::class, mappedBy: 'basket', cascade: ['persist', 'remove'])]
    private Collection $items;

    #[ORM\ManyToOne(targetEntity: DiscountCode::class)]
    private ?DiscountCode $discountCode = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, BasketItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(BasketItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setBasket($this);
        }

        return $this;
    }

    public function removeItem(BasketItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getBasket() === $this) {
                $item->setBasket(null);
            }
        }

        return $this;
    }

    public function hasProduct(Product $product): bool
    {
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                return true;
            }
        }

        return false;
    }

    public function getItemForProduct(Product $product): ?BasketItem
    {
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                return $item;
            }
        }

        return null;
    }

    public function getItemCount(): int
    {
        return $this->items->count();
    }

    public function getTotalQuantity(): int
    {
        $totalQuantity = 0;

        foreach ($this->items as $item) {
            $totalQuantity += $item->getQuantity();
        }

        return $totalQuantity;
    }

    public function getDiscountCode(): ?DiscountCode
    {
        return $this->discountCode;
    }

    public function setDiscountCode(?DiscountCode $discountCode): static
    {
        $this->discountCode = $discountCode;
        return $this;
    }

    public function getTotalPrice(): float
    {
        $totalPrice = 0.0;

        foreach ($this->items as $item) {
            $totalPrice += $item->getPrice() * $item->getQuantity();
        }

        return ceil($totalPrice);
    }

    public function getTotalPriceTtc(): float
    {
        $totalPriceTtc = 0.0;

        foreach ($this->items as $item) {
            $totalPriceTtc += $item->getPriceTva() * $item->getQuantity();
        }

        return floor($totalPriceTtc);
    }

    public function getTotalPriceWithDiscount(): float
    {
        $totalPriceHt = $this->getTotalPrice();

        if ($this->discountCode && $this->discountCode->isActive()) {
            $reductionPercentage = $this->discountCode->getReduction();
            $totalPriceHt -= $totalPriceHt * ($reductionPercentage / 100);
        }

        return ceil($totalPriceHt);
    }

    public function getTotalPriceTtcWithDiscount(): float
    {
        $totalPriceTtc = $this->getTotalPriceTtc();

        if ($this->discountCode && $this->discountCode->isActive()) {
            $reductionPercentage = $this->discountCode->getReduction();
            $totalPriceTtc -= $totalPriceTtc * ($reductionPercentage / 100);
        }

        return ceil($totalPriceTtc);
    }

    public function getAppliedDiscountAmount(): ?float
    {
        if ($this->discountCode && $this->discountCode->isActive()) {
            return $this->discountCode->getReduction();
        }

        return null;
    }
}
