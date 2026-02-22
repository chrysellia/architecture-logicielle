<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Product;
use App\Domain\ValueObject\Money;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;
    
    public function remove(Product $product): void;
    
    public function findById(int $id): ?Product;
    
    public function findBySku(string $sku): ?Product;
    
    public function findAll(): array;
    
    public function findActive(): array;
    
    public function findByCategory(int $categoryId): array;
    
    public function findLowStock(): array;
    
    public function findByName(string $name): array;
    
    public function findByPriceRange(Money $minPrice, Money $maxPrice): array;
    
    public function findAvailable(): array;
    
    public function countActive(): int;
    
    public function countByCategory(int $categoryId): int;
    
    public function countLowStock(): int;
    
    public function getPaginated(int $page = 1, int $limit = 20, array $filters = []): array;
    
    public function search(string $query, array $filters = []): array;
}
