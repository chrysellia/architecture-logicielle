<?php

namespace App\Presentation\Controller\API;

use App\Application\Service\SimpleDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class DataController extends AbstractController
{
    private SimpleDataService $dataService;

    public function __construct(SimpleDataService $dataService)
    {
        $this->dataService = $dataService;
    }

    #[Route('/orders', methods: ['GET'])]
    public function getOrders(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->dataService->getOrders(),
            'message' => 'Orders retrieved successfully'
        ]);
    }

    #[Route('/customers', methods: ['GET'])]
    public function getCustomers(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->dataService->getCustomers(),
            'message' => 'Customers retrieved successfully'
        ]);
    }

    #[Route('/invoices', methods: ['GET'])]
    public function getInvoices(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->dataService->getInvoices(),
            'message' => 'Invoices retrieved successfully'
        ]);
    }

    #[Route('/stock-movements', methods: ['GET'])]
    public function getStockMovements(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->dataService->getStockMovements(),
            'message' => 'Stock movements retrieved successfully'
        ]);
    }

    #[Route('/dashboard/stats', methods: ['GET'])]
    public function getDashboardStats(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->dataService->getDashboardStats(),
            'message' => 'Dashboard statistics retrieved successfully'
        ]);
    }
}
