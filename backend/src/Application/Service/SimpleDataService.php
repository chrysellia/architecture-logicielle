<?php

namespace App\Application\Service;

class SimpleDataService
{
    public function getOrders(): array
    {
        return [
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
                'totalAmount' => 1329.98,
                'status' => 'confirmed',
                'orderDate' => '2024-02-20 10:30:00',
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
                ]
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
                'totalAmount' => 179.97,
                'status' => 'processing',
                'orderDate' => '2024-02-21 14:15:00',
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
                ]
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
                'totalAmount' => 59.98,
                'status' => 'shipped',
                'orderDate' => '2024-02-19 09:45:00',
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
                ]
            ]
        ];
    }

    public function getCustomers(): array
    {
        return [
            [
                'id' => 1,
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'email' => 'jean.dupont@email.com',
                'phone' => '06 12 34 56 78',
                'address' => '123 Rue de la République',
                'city' => 'Paris',
                'postalCode' => '75001',
                'country' => 'France',
                'createdAt' => '2024-01-15 10:00:00'
            ],
            [
                'id' => 2,
                'firstName' => 'Marie',
                'lastName' => 'Martin',
                'email' => 'marie.martin@email.com',
                'phone' => '06 23 45 67 89',
                'address' => '45 Avenue des Champs-Élysées',
                'city' => 'Paris',
                'postalCode' => '75008',
                'country' => 'France',
                'createdAt' => '2024-01-20 14:30:00'
            ],
            [
                'id' => 3,
                'firstName' => 'Pierre',
                'lastName' => 'Bernard',
                'email' => 'pierre.bernard@email.com',
                'phone' => '06 34 56 78 90',
                'address' => '78 Boulevard Saint-Germain',
                'city' => 'Paris',
                'postalCode' => '75005',
                'country' => 'France',
                'createdAt' => '2024-02-01 09:15:00'
            ]
        ];
    }

    public function getInvoices(): array
    {
        return [
            [
                'id' => 1,
                'invoiceNumber' => 'INV-2024-001',
                'order' => [
                    'id' => 1,
                    'orderNumber' => 'ORD-2024-001'
                ],
                'totalAmount' => 1595.98,
                'taxAmount' => 266.00,
                'netAmount' => 1329.98,
                'status' => 'paid',
                'issueDate' => '2024-02-20 10:30:00',
                'dueDate' => '2024-03-20 10:30:00',
                'paidDate' => '2024-02-22 15:45:00'
            ],
            [
                'id' => 2,
                'invoiceNumber' => 'INV-2024-002',
                'order' => [
                    'id' => 2,
                    'orderNumber' => 'ORD-2024-002'
                ],
                'totalAmount' => 215.97,
                'taxAmount' => 36.00,
                'netAmount' => 179.97,
                'status' => 'sent',
                'issueDate' => '2024-02-21 14:15:00',
                'dueDate' => '2024-03-21 14:15:00',
                'paidDate' => null
            ],
            [
                'id' => 3,
                'invoiceNumber' => 'INV-2024-003',
                'order' => [
                    'id' => 3,
                    'orderNumber' => 'ORD-2024-003'
                ],
                'totalAmount' => 71.98,
                'taxAmount' => 12.00,
                'netAmount' => 59.98,
                'status' => 'overdue',
                'issueDate' => '2024-02-19 09:45:00',
                'dueDate' => '2024-03-19 09:45:00',
                'paidDate' => null
            ]
        ];
    }

    public function getStockMovements(): array
    {
        return [
            [
                'id' => 1,
                'product' => [
                    'id' => 1,
                    'name' => 'Laptop Pro 15"',
                    'sku' => 'LP-15-001'
                ],
                'type' => 'in',
                'quantity' => 20,
                'reason' => 'purchase',
                'unitCost' => 999.99,
                'totalCost' => 19999.80,
                'reference' => 'PO-2024-001',
                'movementDate' => '2024-02-15 09:00:00',
                'notes' => 'Initial stock purchase'
            ],
            [
                'id' => 2,
                'product' => [
                    'id' => 2,
                    'name' => 'Mouse Wireless',
                    'sku' => 'MW-001'
                ],
                'type' => 'in',
                'quantity' => 50,
                'reason' => 'purchase',
                'unitCost' => 15.99,
                'totalCost' => 799.50,
                'reference' => 'PO-2024-002',
                'movementDate' => '2024-02-16 10:30:00',
                'notes' => 'Bulk purchase'
            ],
            [
                'id' => 3,
                'product' => [
                    'id' => 1,
                    'name' => 'Laptop Pro 15"',
                    'sku' => 'LP-15-001'
                ],
                'type' => 'out',
                'quantity' => 1,
                'reason' => 'sale',
                'unitCost' => null,
                'totalCost' => null,
                'reference' => 'ORD-2024-001',
                'movementDate' => '2024-02-20 10:30:00',
                'notes' => 'Sold to Jean Dupont'
            ],
            [
                'id' => 4,
                'product' => [
                    'id' => 2,
                    'name' => 'Mouse Wireless',
                    'sku' => 'MW-001'
                ],
                'type' => 'out',
                'quantity' => 1,
                'reason' => 'sale',
                'unitCost' => null,
                'totalCost' => null,
                'reference' => 'ORD-2024-001',
                'movementDate' => '2024-02-20 10:30:00',
                'notes' => 'Sold to Jean Dupont'
            ],
            [
                'id' => 5,
                'product' => [
                    'id' => 3,
                    'name' => 'Keyboard Mechanical',
                    'sku' => 'KM-001'
                ],
                'type' => 'adjustment',
                'quantity' => -5,
                'reason' => 'damage',
                'unitCost' => null,
                'totalCost' => null,
                'reference' => 'INV-ADJ-001',
                'movementDate' => '2024-02-18 15:45:00',
                'notes' => 'Damaged items removed from stock'
            ]
        ];
    }

    public function getDashboardStats(): array
    {
        $products = $this->getProducts();
        $orders = $this->getOrders();
        $customers = $this->getCustomers();
        $invoices = $this->getInvoices();

        $totalRevenue = array_sum(array_column($invoices, 'netAmount'));
        $paidInvoices = array_filter($invoices, fn($inv) => $inv['status'] === 'paid');
        $totalPaid = array_sum(array_column($paidInvoices, 'netAmount'));

        return [
            'products' => [
                'total' => count($products),
                'active' => count(array_filter($products, fn($p) => $p['status'] === 'active')),
                'lowStock' => count(array_filter($products, fn($p) => $p['status'] === 'low_stock')),
                'outOfStock' => count(array_filter($products, fn($p) => $p['status'] === 'out_of_stock'))
            ],
            'orders' => [
                'total' => count($orders),
                'pending' => count(array_filter($orders, fn($o) => $o['status'] === 'pending')),
                'confirmed' => count(array_filter($orders, fn($o) => $o['status'] === 'confirmed')),
                'processing' => count(array_filter($orders, fn($o) => $o['status'] === 'processing')),
                'shipped' => count(array_filter($orders, fn($o) => $o['status'] === 'shipped')),
                'delivered' => count(array_filter($orders, fn($o) => $o['status'] === 'delivered'))
            ],
            'customers' => [
                'total' => count($customers),
                'newThisMonth' => count(array_filter($customers, fn($c) => strtotime($c['createdAt']) > strtotime('2024-02-01')))
            ],
            'invoices' => [
                'total' => count($invoices),
                'paid' => count(array_filter($invoices, fn($i) => $i['status'] === 'paid')),
                'sent' => count(array_filter($invoices, fn($i) => $i['status'] === 'sent')),
                'overdue' => count(array_filter($invoices, fn($i) => $i['status'] === 'overdue')),
                'totalRevenue' => $totalRevenue,
                'totalPaid' => $totalPaid,
                'outstanding' => $totalRevenue - $totalPaid
            ]
        ];
    }

    private function getProducts(): array
    {
        return [
            ['id' => 1, 'name' => 'Laptop Pro 15"', 'sku' => 'LP-15-001', 'status' => 'active'],
            ['id' => 2, 'name' => 'Mouse Wireless', 'sku' => 'MW-001', 'status' => 'low_stock'],
            ['id' => 3, 'name' => 'Keyboard Mechanical', 'sku' => 'KM-001', 'status' => 'out_of_stock']
        ];
    }
}
