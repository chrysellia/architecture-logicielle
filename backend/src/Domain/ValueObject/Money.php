<?php

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as Embeddable;

#[Embeddable]
class Money
{
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    public function __construct(float $amount, string $currency = 'EUR')
    {
        $this->amount = number_format($amount, 2, '.', '');
        $this->currency = strtoupper($currency);
    }

    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->getAmount(), 2, ',', ' ');
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getAmountInCents(): int
    {
        return (int) ($this->amount * 100);
    }

    public function add(Money $other): Money
    {
        $this->validateSameCurrency($other);
        
        return new Money(
            $this->getAmount() + $other->getAmount(),
            $this->currency
        );
    }

    public function subtract(Money $other): Money
    {
        $this->validateSameCurrency($other);
        
        return new Money(
            $this->getAmount() - $other->getAmount(),
            $this->currency
        );
    }

    public function multiply(float $multiplier): Money
    {
        return new Money(
            $this->getAmount() * $multiplier,
            $this->currency
        );
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function greaterThan(Money $other): bool
    {
        $this->validateSameCurrency($other);
        return $this->getAmount() > $other->getAmount();
    }

    public function greaterThanOrEqual(Money $other): bool
    {
        $this->validateSameCurrency($other);
        return $this->getAmount() >= $other->getAmount();
    }

    public function lessThan(Money $other): bool
    {
        $this->validateSameCurrency($other);
        return $this->getAmount() < $other->getAmount();
    }

    public function lessThanOrEqual(Money $other): bool
    {
        $this->validateSameCurrency($other);
        return $this->getAmount() <= $other->getAmount();
    }

    public function isZero(): bool
    {
        return $this->getAmount() === 0.0;
    }

    public function isPositive(): bool
    {
        return $this->getAmount() > 0;
    }

    public function isNegative(): bool
    {
        return $this->getAmount() < 0;
    }

    private function validateSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot operate on Money with different currencies');
        }
    }

    public function toString(): string
    {
        return sprintf('%s %s', $this->getFormattedAmount(), $this->currency);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount(),
            'formattedAmount' => $this->getFormattedAmount(),
            'currency' => $this->currency,
            'amountInCents' => $this->getAmountInCents(),
            'display' => $this->toString(),
        ];
    }

    // Factory methods
    public static function fromCents(int $cents, string $currency = 'EUR'): self
    {
        return new self($cents / 100, $currency);
    }

    public static function zero(string $currency = 'EUR'): self
    {
        return new self(0.0, $currency);
    }

    public static function EUR(float $amount): self
    {
        return new self($amount, 'EUR');
    }

    public static function USD(float $amount): self
    {
        return new self($amount, 'USD');
    }

    public static function GBP(float $amount): self
    {
        return new self($amount, 'GBP');
    }
}
