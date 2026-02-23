<?php

namespace App\Presentation\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SimpleInvoiceController extends AbstractController
{
    private string $storageFile;

    public function __construct()
    {
        $this->storageFile = '/tmp/invoices.json';
    }

    private function ensureStorageInitialized(): void
    {
        // Ne réinitialiser que si le fichier n'existe vraiment pas ou est vide
        if (!file_exists($this->storageFile) || filesize($this->storageFile) === 0) {
            $initialInvoices = [
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
                    'issueDate' => '2024-02-20T10:30:00Z',
                    'dueDate' => '2024-03-20T10:30:00Z',
                    'paidDate' => '2024-02-22T15:45:00Z'
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
                    'issueDate' => '2024-02-21T14:15:00Z',
                    'dueDate' => '2024-03-21T14:15:00Z',
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
                    'issueDate' => '2024-02-19T09:45:00Z',
                    'dueDate' => '2024-03-19T09:45:00Z',
                    'paidDate' => null
                ]
            ];
            file_put_contents($this->storageFile, json_encode($initialInvoices));
        }
    }

    private function getInvoices(): array
    {
        $this->ensureStorageInitialized();
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?: [];
    }

    private function saveInvoices(array $invoices): void
    {
        $this->ensureStorageInitialized();
        file_put_contents($this->storageFile, json_encode($invoices));
    }

    private function getNextId(): int
    {
        $invoices = $this->getInvoices();
        $maxId = 0;
        foreach ($invoices as $invoice) {
            if ($invoice['id'] > $maxId) {
                $maxId = $invoice['id'];
            }
        }
        return $maxId + 1;
    }

    private function getNextInvoiceNumber(): string
    {
        $invoices = $this->getInvoices();
        $maxNumber = 0;
        foreach ($invoices as $invoice) {
            if (preg_match('/INV-(\d{4})-(\d{3})/', $invoice['invoiceNumber'], $matches)) {
                $number = (int)$matches[2];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }
        return 'INV-' . date('Y') . '-' . str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    #[Route('/invoices', methods: ['GET'])]
    public function getInvoicesAction(): JsonResponse
    {
        $this->ensureStorageInitialized();
        $invoices = $this->getInvoices();
        
        return new JsonResponse([
            'success' => true,
            'data' => $invoices,
            'message' => 'Invoices retrieved successfully'
        ]);
    }

    #[Route('/invoices/{id}', methods: ['GET'])]
    public function getInvoiceAction(int $id): JsonResponse
    {
        $invoices = $this->getInvoices();
        foreach ($invoices as $invoice) {
            if ($invoice['id'] === $id) {
                return new JsonResponse([
                    'success' => true,
                    'data' => $invoice,
                    'message' => 'Invoice retrieved successfully'
                ]);
            }
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Invoice not found'
        ], 404);
    }

    #[Route('/invoices', methods: ['POST'])]
    public function createInvoiceAction(Request $request): JsonResponse
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
        if (!isset($data['orderId']) || !isset($data['items']) || empty($data['items'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: orderId, items'
            ], 400);
        }

        // Récupérer les commandes pour avoir les informations
        $ordersFile = '/tmp/orders.json';
        $orders = [];
        if (file_exists($ordersFile)) {
            $ordersContent = file_get_contents($ordersFile);
            $orders = json_decode($ordersContent, true) ?: [];
        }

        $order = null;
        foreach ($orders as $o) {
            if ($o['id'] === (int)$data['orderId']) {
                $order = $o;
                break;
            }
        }

        if (!$order) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Order not found',
                'details' => [
                    'requestedOrderId' => $data['orderId'],
                    'availableOrders' => array_map(function($o) {
                        return ['id' => $o['id'], 'orderNumber' => $o['orderNumber']];
                    }, $orders)
                ]
            ], 404);
        }

        // Calculer le total
        $netAmount = 0;
        foreach ($data['items'] as $item) {
            $netAmount += ($item['quantity'] ?? 1) * ($item['unitPrice'] ?? 0);
        }
        $taxAmount = $data['taxAmount'] ?? 0;
        $totalAmount = $netAmount + $taxAmount;

        $invoice = [
            'id' => $this->getNextId(),
            'invoiceNumber' => $data['invoiceNumber'] ?? $this->getNextInvoiceNumber(),
            'order' => $order,
            'totalAmount' => $totalAmount,
            'taxAmount' => $taxAmount,
            'netAmount' => $netAmount,
            'status' => $data['status'] ?? 'sent',
            'issueDate' => $data['issueDate'] ?? date('c'),
            'dueDate' => $data['dueDate'] ?? date('Y-m-d', strtotime('+30 days')),
            'paidDate' => $data['paidDate'] ?? null,
            'items' => $data['items'],
            'notes' => $data['notes'] ?? ''
        ];

        $invoices = $this->getInvoices();
        $invoices[] = $invoice;
        $this->saveInvoices($invoices);
        
        return new JsonResponse([
            'success' => true,
            'data' => $invoice,
            'message' => 'Invoice created successfully'
        ], 201);
    }

    #[Route('/invoices/{id}', methods: ['PUT'])]
    public function updateInvoiceAction(Request $request, int $id): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], 400);
        }

        $invoices = $this->getInvoices();
        $found = false;
        foreach ($invoices as &$invoice) {
            if ((int)$invoice['id'] === (int)$id) {
                $found = true;
                
                // Log pour déboguer
                error_log("Found invoice with ID: " . $id . " Current taxAmount: " . $invoice['taxAmount']);
                error_log("New taxAmount from request: " . ($data['taxAmount'] ?? 'not set'));
                
                // Mettre à jour tous les champs possibles
                if (isset($data['status'])) $invoice['status'] = $data['status'];
                if (isset($data['notes'])) $invoice['notes'] = $data['notes'];
                if (isset($data['paidDate'])) $invoice['paidDate'] = $data['paidDate'];
                if (isset($data['issueDate'])) $invoice['issueDate'] = $data['issueDate'];
                if (isset($data['dueDate'])) $invoice['dueDate'] = $data['dueDate'];
                if (isset($data['taxAmount'])) {
                    $invoice['taxAmount'] = (float)$data['taxAmount'];
                    error_log("Updated taxAmount to: " . $invoice['taxAmount']);
                }
                
                // Si les items sont fournis, recalculer les montants
                if (isset($data['items']) && !empty($data['items'])) {
                    $invoice['items'] = $data['items'];
                    $netAmount = 0;
                    foreach ($data['items'] as $item) {
                        $netAmount += ($item['quantity'] ?? 1) * ($item['unitPrice'] ?? 0);
                    }
                    $invoice['netAmount'] = $netAmount;
                    $taxAmount = isset($data['taxAmount']) ? (float)$data['taxAmount'] : (float)$invoice['taxAmount'];
                    $invoice['taxAmount'] = $taxAmount;
                    $invoice['totalAmount'] = $netAmount + $taxAmount;
                } else if (isset($data['taxAmount'])) {
                    // Si seulement le taxAmount est mis à jour, recalculer le total
                    $taxAmount = (float)$data['taxAmount'];
                    $invoice['taxAmount'] = $taxAmount;
                    $invoice['totalAmount'] = $invoice['netAmount'] + $taxAmount;
                }
                
                break;
            }
        }
        
        if (!$found) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
        
        $this->saveInvoices($invoices);
        
        return new JsonResponse([
            'success' => true,
            'data' => $invoice,
            'message' => 'Invoice updated successfully'
        ]);
    }

    #[Route('/invoices/{id}/download', methods: ['GET'])]
    public function downloadInvoiceAction(int $id): JsonResponse
    {
        $invoices = $this->getInvoices();
        foreach ($invoices as $invoice) {
            if ($invoice['id'] === $id) {
                // Générer un contenu HTML simple pour la facture
                $html = $this->generateInvoiceHtml($invoice);
                
                return new JsonResponse([
                    'success' => true,
                    'data' => [
                        'id' => $invoice['id'],
                        'invoiceNumber' => $invoice['invoiceNumber'],
                            'html' => $html,
                            'downloadUrl' => 'data:text/html;charset=utf-8,' . rawurlencode($html)
                    ],
                    'message' => 'Invoice generated successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Invoice not found'
        ], 404);
    }

    private function generateInvoiceHtml(array $invoice): string
    {
        $items = $invoice['items'] ?? [];
        $itemsHtml = '';
        
        foreach ($items as $item) {
            $itemsHtml .= sprintf('
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">%s</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">%d</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">€%.2f</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">€%.2f</td>
                </tr>',
                htmlspecialchars($item['description'] ?? ''),
                $item['quantity'] ?? 1,
                $item['unitPrice'] ?? 0,
                $item['totalPrice'] ?? 0
            );
        }
        
        return sprintf('
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Facture %s</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .invoice-info { margin-bottom: 30px; }
                table { width: 100%%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
                th { background-color: #f5f5f5; }
                .total { text-align: right; font-weight: bold; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>FACTURE</h1>
                <h2>N° %s</h2>
            </div>
            
            <div class="invoice-info">
                <p><strong>Commande:</strong> %s</p>
                <p><strong>Date d&#39;&#233;mission:</strong> %s</p>
                <p><strong>Date d&#39;&#233;ch&#39;&#233;ance:</strong> %s</p>
                <p><strong>Statut:</strong> %s</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    %s
                </tbody>
            </table>
            
            <div class="total">
                <p>Montant HT: €%.2f</p>
                <p>TVA: €%.2f</p>
                <p><strong>Total TTC: €%.2f</strong></p>
            </div>
            
            <div class="footer">
                <p>Merci pour votre confiance</p>
            </div>
        </body>
        </html>',
            htmlspecialchars($invoice['invoiceNumber']),
            htmlspecialchars($invoice['invoiceNumber']),
            htmlspecialchars($invoice['order']['orderNumber'] ?? ''),
            date('d/m/Y', strtotime($invoice['issueDate'])),
            date('d/m/Y', strtotime($invoice['dueDate'])),
            htmlspecialchars($invoice['status']),
            $itemsHtml,
            $invoice['netAmount'] ?? 0,
            $invoice['taxAmount'] ?? 0,
            $invoice['totalAmount'] ?? 0
        );
    }

    #[Route('/invoices/{id}', methods: ['DELETE'])]
    public function deleteInvoiceAction(int $id): JsonResponse
    {
        $invoices = $this->getInvoices();
        foreach ($invoices as $key => $invoice) {
            if ($invoice['id'] === $id) {
                unset($invoices[$key]);
                $invoices = array_values($invoices);
                $this->saveInvoices($invoices);
                
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Invoice deleted successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Invoice not found'
        ], 404);
    }
}
