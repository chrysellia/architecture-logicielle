<?php

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    #[Assert\NotBlank(message: "Product name is required")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Product name must be at least {{ limit }} characters", maxMessage: "Product name cannot exceed {{ limit }} characters")]
    public ?string $name = null;

    public ?string $description = null;

    #[Assert\NotBlank(message: "SKU is required")]
    #[Assert\Length(min: 3, max: 100, minMessage: "SKU must be at least {{ limit }} characters", maxMessage: "SKU cannot exceed {{ limit }} characters")]
    #[Assert\Regex(pattern: "/^[A-Z0-9-_]+$/", message: "SKU must contain only uppercase letters, numbers, hyphens and underscores")]
    public ?string $sku = null;

    #[Assert\NotBlank(message: "Category is required")]
    #[Assert\Type(type: "integer", message: "Category ID must be an integer")]
    public ?int $categoryId = null;

    #[Assert\NotBlank(message: "Price is required")]
    #[Assert\Type(type: "float", message: "Price must be a number")]
    #[Assert\Positive(message: "Price must be greater than 0")]
    public ?float $price = null;

    #[Assert\Type(type: "integer", message: "Stock quantity must be an integer")]
    #[Assert\GreaterThanOrEqual(0, message: "Stock quantity cannot be negative")]
    public int $stockQuantity = 0;

    #[Assert\Type(type: "integer", message: "Minimum stock level must be an integer")]
    #[Assert\GreaterThanOrEqual(0, message: "Minimum stock level cannot be negative")]
    public int $minStockLevel = 10;

    #[Assert\Type(type: "bool", message: "Active status must be a boolean")]
    public bool $isActive = true;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->name = $data['name'] ?? null;
        $dto->description = $data['description'] ?? null;
        $dto->sku = $data['sku'] ?? null;
        $dto->categoryId = $data['categoryId'] ?? null;
        $dto->price = $data['price'] ?? null;
        $dto->stockQuantity = $data['stockQuantity'] ?? 0;
        $dto->minStockLevel = $data['minStockLevel'] ?? 10;
        $dto->isActive = $data['isActive'] ?? true;

        return $dto;
    }

    public static function fromProduct($product): self
    {
        $dto = new self();
        $dto->name = $product->getName();
        $dto->description = $product->getDescription();
        $dto->sku = $product->getSku();
        $dto->categoryId = $product->getCategory()->getId();
        $dto->price = $product->getPrice()->getAmount();
        $dto->stockQuantity = $product->getStockQuantity();
        $dto->minStockLevel = $product->getMinStockLevel();
        $dto->isActive = $product->isActive();

        return $dto;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'categoryId' => $this->categoryId,
            'price' => $this->price,
            'stockQuantity' => $this->stockQuantity,
            'minStockLevel' => $this->minStockLevel,
            'isActive' => $this->isActive,
        ];
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors['name'] = 'Product name is required';
        } elseif (strlen($this->name) < 2) {
            $errors['name'] = 'Product name must be at least 2 characters';
        } elseif (strlen($this->name) > 255) {
            $errors['name'] = 'Product name cannot exceed 255 characters';
        }

        if (empty($this->sku)) {
            $errors['sku'] = 'SKU is required';
        } elseif (strlen($this->sku) < 3) {
            $errors['sku'] = 'SKU must be at least 3 characters';
        } elseif (strlen($this->sku) > 100) {
            $errors['sku'] = 'SKU cannot exceed 100 characters';
        } elseif (!preg_match('/^[A-Z0-9-_]+$/', $this->sku)) {
            $errors['sku'] = 'SKU must contain only uppercase letters, numbers, hyphens and underscores';
        }

        if ($this->categoryId === null) {
            $errors['categoryId'] = 'Category is required';
        }

        if ($this->price === null) {
            $errors['price'] = 'Price is required';
        } elseif (!is_numeric($this->price) || $this->price <= 0) {
            $errors['price'] = 'Price must be a positive number';
        }

        if (!is_int($this->stockQuantity) || $this->stockQuantity < 0) {
            $errors['stockQuantity'] = 'Stock quantity must be a non-negative integer';
        }

        if (!is_int($this->minStockLevel) || $this->minStockLevel < 0) {
            $errors['minStockLevel'] = 'Minimum stock level must be a non-negative integer';
        }

        return $errors;
    }

    public function isValid(): bool
    {
        return empty($this->validate());
    }
}
