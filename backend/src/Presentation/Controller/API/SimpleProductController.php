<?php

namespace App\Presentation\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Application\Service\SimpleProductService;

#[Route('/api')]
class SimpleProductController extends AbstractController
{
    private SimpleProductService $productService;
    private string $storageFile;

    public function __construct(SimpleProductService $productService)
    {
        $this->productService = $productService;
        $this->storageFile = '/tmp/products.json';
        $this->initializeStorage();
    }

    private function initializeStorage(): void
    {
        if (!file_exists($this->storageFile)) {
            $initialProducts = [
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
            file_put_contents($this->storageFile, json_encode($initialProducts));
        }
    }

    private function getProducts(): array
    {
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?: [];
    }

    private function saveProducts(array $products): void
    {
        file_put_contents($this->storageFile, json_encode($products));
    }

    private function getNextId(): int
    {
        $products = $this->getProducts();
        $maxId = 0;
        foreach ($products as $product) {
            if ($product['id'] > $maxId) {
                $maxId = $product['id'];
            }
        }
        return $maxId + 1;
    }

    #[Route('/products', methods: ['GET'])]
    public function getProductsAction(): JsonResponse
    {
        $products = $this->getProducts();
        
        return new JsonResponse([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
        ]);
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function getProductAction(int $id): JsonResponse
    {
        $products = $this->getProducts();
        foreach ($products as $product) {
            if ($product['id'] === $id) {
                return new JsonResponse([
                    'success' => true,
                    'data' => $product,
                    'message' => 'Product retrieved successfully'
                ]);
            }
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    #[Route('/products', methods: ['POST'])]
    public function createProductAction(Request $request): JsonResponse
    {
        $content = $request->getContent();
        
        $data = json_decode($content, true);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data',
                'content' => $content,
                'error' => json_last_error_msg()
            ], 400);
        }

        $products = $this->getProducts();
        $product = [
            'id' => $this->getNextId(),
            'name' => $data['name'] ?? 'Test Product',
            'sku' => $data['sku'] ?? 'TEST-001',
            'description' => $data['description'] ?? 'Test Description',
            'price' => (float)($data['price'] ?? 99.99),
            'stock' => (int)($data['stock'] ?? 10),
            'active' => (bool)($data['active'] ?? true)
        ];

        $products[] = $product;
        $this->saveProducts($products);
        
        return new JsonResponse([
            'success' => true,
            'data' => $product,
            'message' => 'Product created successfully'
        ], 201);
    }

    #[Route('/products/{id}', methods: ['PUT'])]
    public function updateProductAction(Request $request, int $id): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], 400);
        }

        $products = $this->getProducts();
        foreach ($products as &$product) {
            if ($product['id'] === $id) {
                if (isset($data['name'])) $product['name'] = $data['name'];
                if (isset($data['sku'])) $product['sku'] = $data['sku'];
                if (isset($data['description'])) $product['description'] = $data['description'];
                if (isset($data['price'])) $product['price'] = (float)$data['price'];
                if (isset($data['stock'])) $product['stock'] = (int)$data['stock'];
                if (isset($data['active'])) $product['active'] = (bool)$data['active'];
                
                $this->saveProducts($products);
                
                return new JsonResponse([
                    'success' => true,
                    'data' => $product,
                    'message' => 'Product updated successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    #[Route('/products/{id}', methods: ['DELETE'])]
    public function deleteProductAction(int $id): JsonResponse
    {
        $products = $this->getProducts();
        foreach ($products as $key => $product) {
            if ($product['id'] === $id) {
                unset($products[$key]);
                $products = array_values($products);
                $this->saveProducts($products);
                
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    #[Route('/test', methods: ['GET'])]
    public function testAction(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => 'API is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
