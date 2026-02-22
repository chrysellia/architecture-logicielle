<?php

namespace App\Application\Service;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Domain\Service\StockManagementService;
use App\Domain\Service\PricingService;
use App\Application\DTO\ProductDTO;
use App\Shared\Exception\NotFoundException;
use App\Shared\Exception\ValidationException;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private StockManagementService $stockManagementService,
        private PricingService $pricingService
    ) {}

    public function createProduct(ProductDTO $dto): Product
    {
        // Validate category exists
        $category = $this->categoryRepository->findById($dto->categoryId);
        if (!$category) {
            throw new NotFoundException('Category not found');
        }

        // Check SKU uniqueness
        if ($this->productRepository->findBySku($dto->sku)) {
            throw new ValidationException('SKU already exists');
        }

        $product = new Product();
        $product->setName($dto->name);
        $product->setDescription($dto->description);
        $product->setSku($dto->sku);
        $product->setCategory($category);
        $product->setPrice(new Money($dto->price));
        $product->setStockQuantity($dto->stockQuantity);
        $product->setMinStockLevel($dto->minStockLevel);
        $product->setActive($dto->isActive);

        $this->productRepository->save($product);

        return $product;
    }

    public function updateProduct(int $id, ProductDTO $dto): Product
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new NotFoundException('Product not found');
        }

        // Validate category exists if provided
        if ($dto->categoryId) {
            $category = $this->categoryRepository->findById($dto->categoryId);
            if (!$category) {
                throw new NotFoundException('Category not found');
            }
            $product->setCategory($category);
        }

        // Check SKU uniqueness if changed
        if ($dto->sku && $dto->sku !== $product->getSku()) {
            $existingProduct = $this->productRepository->findBySku($dto->sku);
            if ($existingProduct && $existingProduct->getId() !== $id) {
                throw new ValidationException('SKU already exists');
            }
            $product->setSku($dto->sku);
        }

        if ($dto->name !== null) {
            $product->setName($dto->name);
        }

        if ($dto->description !== null) {
            $product->setDescription($dto->description);
        }

        if ($dto->price !== null) {
            $product->setPrice(new Money($dto->price));
        }

        if ($dto->stockQuantity !== null) {
            $product->setStockQuantity($dto->stockQuantity);
        }

        if ($dto->minStockLevel !== null) {
            $product->setMinStockLevel($dto->minStockLevel);
        }

        if ($dto->isActive !== null) {
            $product->setActive($dto->isActive);
        }

        $this->productRepository->save($product);

        return $product;
    }

    public function deleteProduct(int $id): void
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new NotFoundException('Product not found');
        }

        // Check if product has orders
        if (!$product->getOrderItems()->isEmpty()) {
            throw new ValidationException('Cannot delete product with existing orders');
        }

        $this->productRepository->remove($product);
    }

    public function getProduct(int $id): Product
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new NotFoundException('Product not found');
        }

        return $product;
    }

    public function getProductBySku(string $sku): Product
    {
        $product = $this->productRepository->findBySku($sku);
        if (!$product) {
            throw new NotFoundException('Product not found');
        }

        return $product;
    }

    public function getAllProducts(array $filters = []): array
    {
        if (empty($filters)) {
            return $this->productRepository->findAll();
        }

        return $this->searchProducts($filters);
    }

    public function getActiveProducts(): array
    {
        return $this->productRepository->findActive();
    }

    public function getAvailableProducts(): array
    {
        return $this->productRepository->findAvailable();
    }

    public function getProductsByCategory(int $categoryId): array
    {
        return $this->productRepository->findByCategory($categoryId);
    }

    public function getLowStockProducts(): array
    {
        return $this->productRepository->findLowStock();
    }

    public function updateStock(int $productId, int $quantity, string $reason = 'Manual adjustment'): Product
    {
        $product = $this->getProduct($productId);
        
        if ($quantity > 0) {
            $this->stockManagementService->increaseStock($product, $quantity, $reason);
        } else {
            $this->stockManagementService->decreaseStock($product, abs($quantity), $reason);
        }

        $this->productRepository->save($product);

        return $product;
    }

    public function adjustPrice(int $productId, float $newPrice): Product
    {
        $product = $this->getProduct($productId);
        
        $oldPrice = $product->getPrice();
        $newMoney = new Money($newPrice, $oldPrice->getCurrency());
        
        $this->pricingService->validatePriceChange($oldPrice, $newMoney);
        
        $product->setPrice($newMoney);
        $this->productRepository->save($product);

        return $product;
    }

    public function searchProducts(array $filters): array
    {
        $query = $filters['query'] ?? '';
        $categoryId = $filters['categoryId'] ?? null;
        $minPrice = $filters['minPrice'] ?? null;
        $maxPrice = $filters['maxPrice'] ?? null;
        $isActive = $filters['isActive'] ?? null;
        $inStock = $filters['inStock'] ?? null;

        if ($query) {
            return $this->productRepository->search($query, $filters);
        }

        $products = $this->productRepository->findAll();

        // Apply filters
        if ($categoryId) {
            $products = array_filter($products, fn($p) => $p->getCategory()->getId() === $categoryId);
        }

        if ($minPrice !== null) {
            $minMoney = new Money($minPrice);
            $products = array_filter($products, fn($p) => $p->getPrice()->greaterThanOrEqual($minMoney));
        }

        if ($maxPrice !== null) {
            $maxMoney = new Money($maxPrice);
            $products = array_filter($products, fn($p) => $p->getPrice()->lessThanOrEqual($maxMoney));
        }

        if ($isActive !== null) {
            $products = array_filter($products, fn($p) => $p->isActive() === $isActive);
        }

        if ($inStock !== null) {
            $products = array_filter($products, fn($p) => $p->isAvailable() === $inStock);
        }

        return array_values($products);
    }

    public function getPaginatedProducts(int $page = 1, int $limit = 20, array $filters = []): array
    {
        return $this->productRepository->getPaginated($page, $limit, $filters);
    }

    public function getProductStatistics(): array
    {
        return [
            'total' => $this->productRepository->findAll(),
            'active' => $this->productRepository->findActive(),
            'lowStock' => $this->productRepository->findLowStock(),
            'outOfStock' => array_filter(
                $this->productRepository->findAll(),
                fn($p) => $p->getStockQuantity() === 0
            ),
        ];
    }

    public function duplicateProduct(int $id, string $newSku, string $newName): Product
    {
        $originalProduct = $this->getProduct($id);

        // Check if new SKU already exists
        if ($this->productRepository->findBySku($newSku)) {
            throw new ValidationException('SKU already exists');
        }

        $newProduct = new Product();
        $newProduct->setName($newName);
        $newProduct->setDescription($originalProduct->getDescription());
        $newProduct->setSku($newSku);
        $newProduct->setCategory($originalProduct->getCategory());
        $newProduct->setPrice($originalProduct->getPrice());
        $newProduct->setStockQuantity(0); // Start with 0 stock
        $newProduct->setMinStockLevel($originalProduct->getMinStockLevel());
        $newProduct->setActive(false); // Start as inactive

        $this->productRepository->save($newProduct);

        return $newProduct;
    }
}
