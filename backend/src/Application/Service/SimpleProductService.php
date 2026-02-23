<?php

namespace App\Application\Service;

class SimpleProductService
{
    private array $products = [];
    private int $nextId = 4;

    public function __construct()
    {
        // Initialisation avec des données de test
        $this->products = [
            [
                'id' => 1,
                'name' => 'Laptop Pro 15"',
                'sku' => 'LP-15-001',
                'description' => 'Laptop professionnel 15 pouces',
                'price' => 1299.99,
                'stock' => 15,
                'active' => true
            ],
            [
                'id' => 2,
                'name' => 'Mouse Wireless',
                'sku' => 'MW-001',
                'description' => 'Souris sans fil',
                'price' => 29.99,
                'stock' => 3,
                'active' => true
            ],
            [
                'id' => 3,
                'name' => 'Keyboard Mechanical',
                'sku' => 'KM-001',
                'description' => 'Clavier mécanique',
                'price' => 89.99,
                'stock' => 0,
                'active' => true
            ]
        ];
    }

    public function createProduct(array $data): array
    {
        $product = [
            'id' => $this->nextId++,
            'name' => $data['name'] ?? 'Test Product',
            'sku' => $data['sku'] ?? 'TEST-001',
            'description' => $data['description'] ?? 'Test Description',
            'price' => (float)($data['price'] ?? 99.99),
            'stock' => (int)($data['stock'] ?? 10),
            'active' => (bool)($data['active'] ?? true)
        ];

        $this->products[] = $product;
        return $product;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getProductById(int $id): ?array
    {
        foreach ($this->products as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }

    public function updateProduct(int $id, array $data): array
    {
        foreach ($this->products as &$product) {
            if ($product['id'] === $id) {
                if (isset($data['name'])) $product['name'] = $data['name'];
                if (isset($data['sku'])) $product['sku'] = $data['sku'];
                if (isset($data['description'])) $product['description'] = $data['description'];
                if (isset($data['price'])) $product['price'] = (float)$data['price'];
                if (isset($data['stock'])) $product['stock'] = (int)$data['stock'];
                if (isset($data['active'])) $product['active'] = (bool)$data['active'];
                return $product;
            }
        }
        return [];
    }

    public function deleteProduct(int $id): bool
    {
        foreach ($this->products as $key => $product) {
            if ($product['id'] === $id) {
                unset($this->products[$key]);
                $this->products = array_values($this->products); // Réindexer
                return true;
            }
        }
        return false;
    }
}
