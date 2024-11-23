<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $user_order = null;

    #[ORM\Column(length: 255)]
    private ?string $code_order = null;

    #[ORM\Column]
    private ?float $total_price_order = null;

    #[ORM\Column]
    private ?int $total_quantity_order = null;

    #[ORM\Column(length: 255)]
    private ?string $payment_order = null;

    #[ORM\Column]
    private ?bool $validate_order = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, OrderDetails>
     */
    #[ORM\OneToMany(targetEntity: OrderDetails::class, mappedBy: 'order_associated')]
    private Collection $orderDetails;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserOrder(): ?User
    {
        return $this->user_order;
    }

    public function setUserOrder(?User $user_order): static
    {
        $this->user_order = $user_order;

        return $this;
    }

    public function getCodeOrder(): ?string
    {
        return $this->code_order;
    }

    public function setCodeOrder(string $code_order): static
    {
        $this->code_order = $code_order;

        return $this;
    }

    public function getTotalPriceOrder(): ?float
    {
        return $this->total_price_order;
    }

    public function setTotalPriceOrder(float $total_price_order): static
    {
        $this->total_price_order = $total_price_order;

        return $this;
    }

    public function getTotalQuantityOrder(): ?int
    {
        return $this->total_quantity_order;
    }

    public function setTotalQuantityOrder(int $total_quantity_order): static
    {
        $this->total_quantity_order = $total_quantity_order;

        return $this;
    }

    public function getPaymentOrder(): ?string
    {
        return $this->payment_order;
    }

    public function setPaymentOrder(string $payment_order): static
    {
        $this->payment_order = $payment_order;

        return $this;
    }

    public function isValidateOrder(): ?bool
    {
        return $this->validate_order;
    }

    public function setValidateOrder(bool $validate_order): static
    {
        $this->validate_order = $validate_order;

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

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setOrderAssociated($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getOrderAssociated() === $this) {
                $orderDetail->setOrderAssociated(null);
            }
        }

        return $this;
    }
}
