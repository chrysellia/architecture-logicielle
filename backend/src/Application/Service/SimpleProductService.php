<?php

namespace App\Application\Service;

use App\Domain\Entity\Product;
use App\Domain\ValueObject\Money;

class SimpleProductService
{
    public function __construct()
    {
        // Service simplifié sans dépendances pour éviter les erreurs d'autowiring
    }

    public function createProduct(array $data): Product
    {
        $product = new Product();
        $product->setName($data['name'] ?? 'Test Product');
        $product->setSku($data['sku'] ?? 'TEST-001');
        $product->setDescription($data['description'] ?? 'Test Description');
        $product->setPrice(new Money($data['price'] ?? 99.99, 'EUR'));
        $product->setStock($data['stock'] ?? 10);
        $product->setActive($data['active'] ?? true);

        return $product;
    }

    public function getProducts(): array
    {
        // Retourne des données de test pour l'instant
        return [
            [
                'id' => 1,
                'name' => 'Laptop Pro 15"',
                'sku' => 'LP-15-001',
                'category' => 'Informatique',
                'price' => 1299.99,
                'stock' => 15,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Mouse Wireless',
                'sku' => 'MW-001',
                'category' => 'Accessoires',
                'price' => 29.99,
                'stock' => 3,
                'status' => 'low_stock'
            ],
            [
                'id' => 3,
                'name' => 'Keyboard Mechanical',
                'sku' => 'KM-001',
                'category' => 'Accessoires',
                'price' => 89.99,
                'stock' => 0,
                'status' => 'out_of_stock'
            ]
        ];
    }

    public function getProductById(int $id): ?array
    {
        $products = $this->getProducts();
        foreach ($products as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }
}
