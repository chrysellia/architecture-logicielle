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

    #[Route('/dashboard/stats', methods: ['GET'])]
    public function getDashboardStatsAction(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->dataService->getDashboardStats(),
            'message' => 'Dashboard statistics retrieved successfully'
        ]);
    }
}
