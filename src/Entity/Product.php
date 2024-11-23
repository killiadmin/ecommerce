<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[AllowDynamicProperties] #[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Vich\Uploadable()]
class  Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[Vich\UploadableField(mapping: 'products', fileNameProperty: 'picture')]
    #[Assert\Image()]
    private ?File $pictureFile = null;

    #[ORM\Column(type: 'float', precision: 10, scale: 2)]
    private ?float $price = null;

    #[ORM\Column(type: 'float', precision: 10, scale: 2)]
    private ?float $price_tva = null;

    #[ORM\Column]
    private ?int $rental_counter = null;

    #[ORM\Column]
    private ?int $active = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, BasketItem>
     */
    #[ORM\OneToMany(targetEntity: BasketItem::class, mappedBy: 'product')]
    private Collection $basketItems;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    /**
     * @var Collection<int, OrderDetails>
     */
    #[ORM\OneToMany(targetEntity: OrderDetails::class, mappedBy: 'product_associated')]
    private Collection $created_at;

    public function __construct()
    {
        $this->basketItems = new ArrayCollection();
        $this->created_at = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getRentalCounter(): ?int
    {
        return $this->rental_counter;
    }

    public function setRentalCounter(int $rental_counter): static
    {
        $this->rental_counter = $rental_counter;

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(int $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, BasketItem>
     */
    public function getBasketItems(): Collection
    {
        return $this->basketItems;
    }

    public function addBasketItem(BasketItem $basketItem): static
    {
        if (!$this->basketItems->contains($basketItem)) {
            $this->basketItems->add($basketItem);
            $basketItem->setProduct($this);
        }

        return $this;
    }

    public function removeBasketItem(BasketItem $basketItem): static
    {
        if ($this->basketItems->removeElement($basketItem)) {
            // set the owning side to null (unless already changed)
            if ($basketItem->getProduct() === $this) {
                $basketItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPictureFile(): ?File
    {
        return $this->pictureFile;
    }

    public function setPictureFile(?File $pictureFile): static
    {
        $this->pictureFile = $pictureFile;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = round($price,2);

        $this->setPriceTva($this->calculatePriceWithTva($price));

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

    private function calculatePriceWithTva(float $price): float
    {
        $priceNumeric = $price;
        $priceWithVat = $priceNumeric * 1.20;

        return round($priceWithVat, 2);
    }

    public function addCreatedAt(OrderDetails $createdAt): static
    {
        if (!$this->created_at->contains($createdAt)) {
            $this->created_at->add($createdAt);
            $createdAt->setProductAssociated($this);
        }

        return $this;
    }

    public function removeCreatedAt(OrderDetails $createdAt): static
    {
        if ($this->created_at->removeElement($createdAt)) {
            // set the owning side to null (unless already changed)
            if ($createdAt->getProductAssociated() === $this) {
                $createdAt->setProductAssociated(null);
            }
        }

        return $this;
    }
}
