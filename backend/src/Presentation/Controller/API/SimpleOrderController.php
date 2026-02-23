<?php

namespace App\Presentation\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SimpleOrderController extends AbstractController
{
    private string $storageFile;

    public function __construct()
    {
        $this->storageFile = '/tmp/orders.json';
    }

    private function ensureStorageInitialized(): void
    {
        // Ne réinitialiser que si le fichier n'existe vraiment pas ou est vide
        if (!file_exists($this->storageFile) || filesize($this->storageFile) === 0) {
            $initialOrders = [
                [
                    'id' => 1,
                    'orderNumber' => 'ORD-2024-001',
                    'customer' => [
                        'id' => 1,
                        'firstName' => 'Jean',
                        'lastName' => 'Dupont',
                        'email' => 'jean.dupont@email.com',
                        'phone' => '06 12 34 56 78'
                    ],
                    'status' => 'confirmed',
                    'totalAmount' => 1329.98,
                    'orderDate' => '2024-02-20T10:30:00Z',
                    'items' => [
                        [
                            'id' => 1,
                            'product' => [
                                'id' => 1,
                                'name' => 'Laptop Pro 15"',
                                'sku' => 'LP-15-001'
                            ],
                            'quantity' => 1,
                            'unitPrice' => 1299.99,
                            'totalPrice' => 1299.99
                        ],
                        [
                            'id' => 2,
                            'product' => [
                                'id' => 2,
                                'name' => 'Mouse Wireless',
                                'sku' => 'MW-001'
                            ],
                            'quantity' => 1,
                            'unitPrice' => 29.99,
                            'totalPrice' => 29.99
                        ]
                    ],
                    'notes' => 'Commande client standard'
                ],
                [
                    'id' => 2,
                    'orderNumber' => 'ORD-2024-002',
                    'customer' => [
                        'id' => 2,
                        'firstName' => 'Marie',
                        'lastName' => 'Martin',
                        'email' => 'marie.martin@email.com',
                        'phone' => '06 23 45 67 89'
                    ],
                    'status' => 'processing',
                    'totalAmount' => 179.97,
                    'orderDate' => '2024-02-21T14:15:00Z',
                    'items' => [
                        [
                            'id' => 3,
                            'product' => [
                                'id' => 3,
                                'name' => 'Keyboard Mechanical',
                                'sku' => 'KM-001'
                            ],
                            'quantity' => 2,
                            'unitPrice' => 89.99,
                            'totalPrice' => 179.97
                        ]
                    ],
                    'notes' => 'Commande en cours de traitement'
                ],
                [
                    'id' => 3,
                    'orderNumber' => 'ORD-2024-003',
                    'customer' => [
                        'id' => 3,
                        'firstName' => 'Pierre',
                        'lastName' => 'Bernard',
                        'email' => 'pierre.bernard@email.com',
                        'phone' => '06 34 56 78 90'
                    ],
                    'status' => 'shipped',
                    'totalAmount' => 59.98,
                    'orderDate' => '2024-02-19T09:45:00Z',
                    'items' => [
                        [
                            'id' => 4,
                            'product' => [
                                'id' => 2,
                                'name' => 'Mouse Wireless',
                                'sku' => 'MW-001'
                            ],
                            'quantity' => 2,
                            'unitPrice' => 29.99,
                            'totalPrice' => 59.98
                        ]
                    ],
                    'notes' => 'Commande expédiée'
                ]
            ];
            file_put_contents($this->storageFile, json_encode($initialOrders));
        }
    }

    private function getOrders(): array
    {
        $this->ensureStorageInitialized();
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?: [];
    }

    private function saveOrders(array $orders): void
    {
        $this->ensureStorageInitialized();
        file_put_contents($this->storageFile, json_encode($orders));
    }

    private function getNextId(): int
    {
        $orders = $this->getOrders();
        $maxId = 0;
        foreach ($orders as $order) {
            if ($order['id'] > $maxId) {
                $maxId = $order['id'];
            }
        }
        return $maxId + 1;
    }

    private function getNextOrderNumber(): string
    {
        $orders = $this->getOrders();
        $maxNumber = 0;
        foreach ($orders as $order) {
            if (preg_match('/ORD-(\d{4})-(\d{3})/', $order['orderNumber'], $matches)) {
                $number = (int)$matches[2];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }
        return 'ORD-' . date('Y') . '-' . str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    #[Route('/orders', methods: ['GET'])]
    public function getOrdersAction(): JsonResponse
    {
        $this->ensureStorageInitialized();
        $orders = $this->getOrders();
        
        return new JsonResponse([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ]);
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    public function getOrderAction(int $id): JsonResponse
    {
        $orders = $this->getOrders();
        foreach ($orders as $order) {
            if ($order['id'] === $id) {
                return new JsonResponse([
                    'success' => true,
                    'data' => $order,
                    'message' => 'Order retrieved successfully'
                ]);
            }
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Order not found'
        ], 404);
    }

    #[Route('/orders', methods: ['POST'])]
    public function createOrderAction(Request $request): JsonResponse
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
        if (!isset($data['customerId']) || !isset($data['items']) || empty($data['items'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: customerId, items'
            ], 400);
        }

        // Récupérer les clients et produits pour avoir les informations
        $customersFile = '/tmp/customers.json';
        $customers = [];
        if (file_exists($customersFile)) {
            $customersContent = file_get_contents($customersFile);
            $customers = json_decode($customersContent, true) ?: [];
        }

        $productsFile = '/tmp/products.json';
        $products = [];
        if (file_exists($productsFile)) {
            $productsContent = file_get_contents($productsFile);
            $products = json_decode($productsContent, true) ?: [];
        }

        $customer = null;
        foreach ($customers as $c) {
            if ($c['id'] === (int)$data['customerId']) {
                $customer = $c;
                break;
            }
        }

        if (!$customer) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Customer not found',
                'details' => [
                    'requestedCustomerId' => $data['customerId'],
                    'availableCustomers' => array_map(function($c) {
                        return ['id' => $c['id'], 'name' => $c['firstName'] . ' ' . $c['lastName']];
                    }, $customers)
                ]
            ], 404);
        }

        // Calculer le total et créer les items
        $orderItems = [];
        $totalAmount = 0;
        foreach ($data['items'] as $index => $item) {
            $product = null;
            foreach ($products as $p) {
                if ($p['id'] === (int)$item['productId']) {
                    $product = $p;
                    break;
                }
            }

            if (!$product) {
                return new JsonResponse([
                    'success' => false,
                    'message' => "Product not found for item {$index}",
                    'details' => [
                        'requestedProductId' => $item['productId'],
                        'availableProducts' => array_map(function($p) {
                            return ['id' => $p['id'], 'name' => $p['name'], 'sku' => $p['sku']];
                        }, $products)
                    ]
                ], 404);
            }

            $quantity = (float)($item['quantity'] ?? 1);
            $unitPrice = (float)($item['unitPrice'] ?? $product['price']);
            $totalPrice = $quantity * $unitPrice;
            $totalAmount += $totalPrice;

            $orderItems[] = [
                'id' => $index + 1,
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'sku' => $product['sku']
                ],
                'quantity' => $quantity,
                'unitPrice' => $unitPrice,
                'totalPrice' => $totalPrice
            ];
        }

        $order = [
            'id' => $this->getNextId(),
            'orderNumber' => $this->getNextOrderNumber(),
            'customer' => [
                'id' => $customer['id'],
                'firstName' => $customer['firstName'],
                'lastName' => $customer['lastName'],
                'email' => $customer['email'],
                'phone' => $customer['phone']
            ],
            'status' => $data['status'] ?? 'pending',
            'totalAmount' => $totalAmount,
            'orderDate' => date('c'),
            'items' => $orderItems,
            'notes' => $data['notes'] ?? ''
        ];

        $orders = $this->getOrders();
        $orders[] = $order;
        $this->saveOrders($orders);
        
        return new JsonResponse([
            'success' => true,
            'data' => $order,
            'message' => 'Order created successfully'
        ], 201);
    }

    #[Route('/orders/{id}', methods: ['PUT'])]
    public function updateOrderAction(Request $request, int $id): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], 400);
        }

        $orders = $this->getOrders();
        foreach ($orders as &$order) {
            if ($order['id'] === $id) {
                if (isset($data['status'])) $order['status'] = $data['status'];
                if (isset($data['notes'])) $order['notes'] = $data['notes'];
                
                $this->saveOrders($orders);
                
                return new JsonResponse([
                    'success' => true,
                    'data' => $order,
                    'message' => 'Order updated successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Order not found'
        ], 404);
    }

    #[Route('/orders/{id}', methods: ['DELETE'])]
    public function deleteOrderAction(int $id): JsonResponse
    {
        $orders = $this->getOrders();
        foreach ($orders as $key => $order) {
            if ($order['id'] === $id) {
                unset($orders[$key]);
                $orders = array_values($orders);
                $this->saveOrders($orders);
                
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Order deleted successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Order not found'
        ], 404);
    }
}
