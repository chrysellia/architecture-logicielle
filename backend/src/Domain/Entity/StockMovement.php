<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stock_movements')]
class StockMovement
{
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const REASON_PURCHASE = 'purchase';
    public const REASON_SALE = 'sale';
    public const REASON_RETURN = 'return';
    public const REASON_DAMAGE = 'damage';
    public const REASON_LOSS = 'loss';
    public const REASON_INVENTORY = 'inventory';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'string', length: 20)]
    private string $type;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $reason;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $unitCost;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $totalCost;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reference;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $movementDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    public function __construct()
    {
        $this->movementDate = new \DateTime();
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        $this->calculateTotal();
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getUnitCost(): ?float
    {
        return $this->unitCost;
    }

    public function setUnitCost(?float $unitCost): self
    {
        $this->unitCost = $unitCost;
        $this->calculateTotal();
        return $this;
    }

    public function getTotalCost(): ?float
    {
        return $this->totalCost;
    }

    public function setTotalCost(?float $totalCost): self
    {
        $this->totalCost = $totalCost;
        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getMovementDate(): \DateTime
    {
        return $this->movementDate;
    }

    public function setMovementDate(\DateTime $movementDate): self
    {
        $this->movementDate = $movementDate;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    private function calculateTotal(): void
    {
        if ($this->unitCost !== null) {
            $this->totalCost = $this->quantity * $this->unitCost;
        }
    }

    public function isIn(): bool
    {
        return $this->type === self::TYPE_IN;
    }

    public function isOut(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    public function isAdjustment(): bool
    {
        return $this->type === self::TYPE_ADJUSTMENT;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product' => $this->product->toArray(),
            'type' => $this->type,
            'quantity' => $this->quantity,
            'reason' => $this->reason,
            'unitCost' => $this->unitCost,
            'totalCost' => $this->totalCost,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'movementDate' => $this->movementDate->format('Y-m-d H:i:s'),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
