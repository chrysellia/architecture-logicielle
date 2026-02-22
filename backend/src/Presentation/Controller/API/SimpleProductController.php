<?php

namespace App\Presentation\Controller\API;

use App\Application\Service\SimpleProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SimpleProductController extends AbstractController
{
    private SimpleProductService $productService;

    public function __construct(SimpleProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        $products = $this->productService->getProducts();
        
        return new JsonResponse([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
        ]);
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function getProduct(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        
        if (!$product) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'data' => $product,
            'message' => 'Product retrieved successfully'
        ]);
    }

    #[Route('/products', methods: ['POST'])]
    public function createProduct(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], 400);
        }

        $product = $this->productService->createProduct($data);
        
        return new JsonResponse([
            'success' => true,
            'data' => $product->toArray(),
            'message' => 'Product created successfully'
        ], 201);
    }

    #[Route('/test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => 'API is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
