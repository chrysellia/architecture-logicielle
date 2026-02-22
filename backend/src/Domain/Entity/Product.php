<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $sku;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\Embedded(class: Money::class)]
    private Money $price;

    #[ORM\Column(type: 'integer')]
    private int $stockQuantity = 0;

    #[ORM\Column(type: 'integer')]
    private int $minStockLevel = 10;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: StockMovement::class)]
    private Collection $stockMovements;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->stockMovements = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): self
    {
        $this->price = $price;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): self
    {
        $this->stockQuantity = max(0, $stockQuantity);
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function getMinStockLevel(): int
    {
        return $this->minStockLevel;
    }

    public function setMinStockLevel(int $minStockLevel): self
    {
        $this->minStockLevel = max(0, $minStockLevel);
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }

    public function addStockMovement(StockMovement $stockMovement): self
    {
        if (!$this->stockMovements->contains($stockMovement)) {
            $this->stockMovements->add($stockMovement);
            $stockMovement->setProduct($this);
        }

        return $this;
    }

    public function removeStockMovement(StockMovement $stockMovement): self
    {
        if ($this->stockMovements->removeElement($stockMovement)) {
            if ($stockMovement->getProduct() === $this) {
                $stockMovement->setProduct(null);
            }
        }

        return $this;
    }

    // Business logic methods
    public function isAvailable(): bool
    {
        return $this->isActive && $this->stockQuantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stockQuantity <= $this->minStockLevel;
    }

    public function canFulfillQuantity(int $quantity): bool
    {
        return $this->stockQuantity >= $quantity;
    }

    public function decreaseStock(int $quantity): self
    {
        if (!$this->canFulfillQuantity($quantity)) {
            throw new \DomainException('Insufficient stock for product: ' . $this->name);
        }

        $this->stockQuantity -= $quantity;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function increaseStock(int $quantity): self
    {
        if ($quantity <= 0) {
            throw new \DomainException('Stock increase quantity must be positive');
        }

        $this->stockQuantity += $quantity;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'category' => $this->category->toArray(),
            'price' => $this->price->toArray(),
            'stockQuantity' => $this->stockQuantity,
            'minStockLevel' => $this->minStockLevel,
            'isActive' => $this->isActive,
            'isAvailable' => $this->isAvailable(),
            'isLowStock' => $this->isLowStock(),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
