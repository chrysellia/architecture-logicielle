<?php

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
class Invoice
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $invoiceNumber;

    #[ORM\OneToOne(targetEntity: Order::class, inversedBy: 'invoice')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $totalAmount;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $taxAmount;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $netAmount;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $issueDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dueDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $paidDate;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Payment::class)]
    private Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->status = self::STATUS_DRAFT;
        $this->issueDate = new \DateTime();
        $this->dueDate = (new \DateTime())->add(new \DateInterval('P30D')); // 30 jours
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(float $taxAmount): self
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getNetAmount(): float
    {
        return $this->netAmount;
    }

    public function setNetAmount(float $netAmount): self
    {
        $this->netAmount = $netAmount;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getIssueDate(): \DateTime
    {
        return $this->issueDate;
    }

    public function setIssueDate(\DateTime $issueDate): self
    {
        $this->issueDate = $issueDate;
        return $this;
    }

    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getPaidDate(): ?\DateTime
    {
        return $this->paidDate;
    }

    public function setPaidDate(?\DateTime $paidDate): self
    {
        $this->paidDate = $paidDate;
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

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setInvoice($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getInvoice() === $this) {
                $payment->setInvoice(null);
            }
        }

        return $this;
    }

    public function calculateTax(float $taxRate = 0.20): void
    {
        $this->taxAmount = $this->netAmount * $taxRate;
        $this->totalAmount = $this->netAmount + $this->taxAmount;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoiceNumber' => $this->invoiceNumber,
            'order' => $this->order->toArray(),
            'totalAmount' => $this->totalAmount,
            'taxAmount' => $this->taxAmount,
            'netAmount' => $this->netAmount,
            'status' => $this->status,
            'issueDate' => $this->issueDate->format('Y-m-d H:i:s'),
            'dueDate' => $this->dueDate->format('Y-m-d H:i:s'),
            'paidDate' => $this->paidDate?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
