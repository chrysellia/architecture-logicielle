<?php

namespace App\Presentation\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SimpleStockMovementController extends AbstractController
{
    private string $storageFile;

    public function __construct()
    {
        $this->storageFile = '/tmp/stock_movements.json';
    }

    private function ensureStorageInitialized(): void
    {
        // Ne réinitialiser que si le fichier n'existe vraiment pas ou est vide
        if (!file_exists($this->storageFile) || filesize($this->storageFile) === 0) {
            $initialMovements = [
                [
                    'id' => 1,
                    'product' => [
                        'id' => 1,
                        'name' => 'Laptop Pro 15"',
                        'sku' => 'LP-15-001'
                    ],
                    'type' => 'in',
                    'quantity' => 15,
                    'reason' => 'purchase',
                    'reference' => 'FACT-2024-001',
                    'unitCost' => 1000.00,
                    'totalCost' => 15000.00,
                    'movementDate' => '2024-02-20T10:30:00Z',
                    'notes' => 'Stock initial'
                ],
                [
                    'id' => 2,
                    'product' => [
                        'id' => 2,
                        'name' => 'Mouse Wireless',
                        'sku' => 'MW-001'
                    ],
                    'type' => 'out',
                    'quantity' => 2,
                    'reason' => 'sale',
                    'reference' => 'CMD-2024-001',
                    'unitCost' => 25.00,
                    'totalCost' => 50.00,
                    'movementDate' => '2024-02-21T14:15:00Z',
                    'notes' => 'Vente client'
                ],
                [
                    'id' => 3,
                    'product' => [
                        'id' => 3,
                        'name' => 'Keyboard Mechanical',
                        'sku' => 'KM-001'
                    ],
                    'type' => 'adjustment',
                    'quantity' => -5,
                    'reason' => 'inventory',
                    'reference' => 'INV-2024-001',
                    'unitCost' => null,
                    'totalCost' => null,
                    'movementDate' => '2024-02-19T09:45:00Z',
                    'notes' => 'Ajustement inventaire'
                ]
            ];
            file_put_contents($this->storageFile, json_encode($initialMovements));
        }
    }

    private function getMovements(): array
    {
        $this->ensureStorageInitialized();
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?: [];
    }

    private function saveMovements(array $movements): void
    {
        $this->ensureStorageInitialized();
        file_put_contents($this->storageFile, json_encode($movements));
    }

    private function getNextId(): int
    {
        $movements = $this->getMovements();
        $maxId = 0;
        foreach ($movements as $movement) {
            if ($movement['id'] > $maxId) {
                $maxId = $movement['id'];
            }
        }
        return $maxId + 1;
    }

    #[Route('/stock-movements', methods: ['GET'])]
    public function getStockMovementsAction(): JsonResponse
    {
        $this->ensureStorageInitialized();
        $movements = $this->getMovements();
        
        // Debug
        error_log('Stock movements from file: ' . print_r($movements, true));
        
        return new JsonResponse([
            'success' => true,
            'data' => $movements,
            'message' => 'Stock movements retrieved successfully'
        ]);
    }

    #[Route('/stock-movements/{id}', methods: ['GET'])]
    public function getStockMovementAction(int $id): JsonResponse
    {
        $movements = $this->getMovements();
        foreach ($movements as $movement) {
            if ($movement['id'] === $id) {
                return new JsonResponse([
                    'success' => true,
                    'data' => $movement,
                    'message' => 'Stock movement retrieved successfully'
                ]);
            }
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Stock movement not found'
        ], 404);
    }

    #[Route('/stock-movements', methods: ['POST'])]
    public function createStockMovementAction(Request $request): JsonResponse
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

        // Validation de base
        if (!isset($data['productId']) || !isset($data['type']) || !isset($data['quantity'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: productId, type, quantity'
            ], 400);
        }

        // Récupérer les produits pour avoir les informations
        $productsFile = '/tmp/products.json';
        $products = [];
        if (file_exists($productsFile)) {
            $productsContent = file_get_contents($productsFile);
            $products = json_decode($productsContent, true) ?: [];
        }

        $product = null;
        foreach ($products as $p) {
            // Comparer en convertissant l'ID demandé en entier pour gérer les strings
            if ($p['id'] === (int)$data['productId']) {
                $product = $p;
                break;
            }
        }

        if (!$product) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Product not found',
                'details' => [
                    'requestedProductId' => $data['productId'],
                    'availableProducts' => array_map(function($p) {
                        return ['id' => $p['id'], 'name' => $p['name'], 'sku' => $p['sku']];
                    }, $products)
                ]
            ], 404);
        }

        $movement = [
            'id' => $this->getNextId(),
            'product' => [
                'id' => $product['id'],
                'name' => $product['name'],
                'sku' => $product['sku']
            ],
            'type' => $data['type'],
            'quantity' => (float)($data['quantity'] ?? 0),
            'reason' => $data['reason'] ?? 'manual',
            'reference' => $data['reference'] ?? '',
            'unitCost' => isset($data['unitCost']) ? (float)$data['unitCost'] : null,
            'totalCost' => isset($data['unitCost']) ? (float)$data['unitCost'] * (float)($data['quantity'] ?? 0) : null,
            'movementDate' => date('c'),
            'notes' => $data['notes'] ?? ''
        ];

        $movements = $this->getMovements();
        $movements[] = $movement;
        $this->saveMovements($movements);
        
        return new JsonResponse([
            'success' => true,
            'data' => $movement,
            'message' => 'Stock movement created successfully'
        ], 201);
    }

    #[Route('/stock-movements/{id}', methods: ['PUT'])]
    public function updateStockMovementAction(Request $request, int $id): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], 400);
        }

        $movements = $this->getMovements();
        foreach ($movements as &$movement) {
            if ($movement['id'] === $id) {
                if (isset($data['type'])) $movement['type'] = $data['type'];
                if (isset($data['quantity'])) $movement['quantity'] = (float)$data['quantity'];
                if (isset($data['reason'])) $movement['reason'] = $data['reason'];
                if (isset($data['reference'])) $movement['reference'] = $data['reference'];
                if (isset($data['unitCost'])) $movement['unitCost'] = (float)$data['unitCost'];
                if (isset($data['notes'])) $movement['notes'] = $data['notes'];
                
                if (isset($data['unitCost']) && isset($data['quantity'])) {
                    $movement['totalCost'] = (float)$data['unitCost'] * (float)$data['quantity'];
                }
                
                $this->saveMovements($movements);
                
                return new JsonResponse([
                    'success' => true,
                    'data' => $movement,
                    'message' => 'Stock movement updated successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Stock movement not found'
        ], 404);
    }

    #[Route('/stock-movements/{id}', methods: ['DELETE'])]
    public function deleteStockMovementAction(int $id): JsonResponse
    {
        $movements = $this->getMovements();
        foreach ($movements as $key => $movement) {
            if ($movement['id'] === $id) {
                unset($movements[$key]);
                $movements = array_values($movements);
                $this->saveMovements($movements);
                
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Stock movement deleted successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Stock movement not found'
        ], 404);
    }
}
