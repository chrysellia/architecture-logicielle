<?php

namespace App\Presentation\Controller\API;

use App\Application\Service\ProductService;
use App\Application\DTO\ProductDTO;
use App\Presentation\Request\CreateProductRequest;
use App\Presentation\Request\UpdateProductRequest;
use App\Presentation\Response\ProductResponse;
use App\Presentation\Response\ErrorResponse;
use App\Shared\Exception\NotFoundException;
use App\Shared\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 20);
            $categoryId = $request->query->get('categoryId');
            $query = $request->query->get('query');
            $minPrice = $request->query->get('minPrice');
            $maxPrice = $request->query->get('maxPrice');
            $isActive = $request->query->get('isActive');
            $inStock = $request->query->get('inStock');

            $filters = array_filter([
                'query' => $query,
                'categoryId' => $categoryId ? (int) $categoryId : null,
                'minPrice' => $minPrice ? (float) $minPrice : null,
                'maxPrice' => $maxPrice ? (float) $maxPrice : null,
                'isActive' => $isActive !== null ? filter_var($isActive, FILTER_VALIDATE_BOOLEAN) : null,
                'inStock' => $inStock !== null ? filter_var($inStock, FILTER_VALIDATE_BOOLEAN) : null,
            ]);

            if ($page > 1 || !empty($filters)) {
                $products = $this->productService->getPaginatedProducts($page, $limit, $filters);
            } else {
                $products = $this->productService->getAllProducts($filters);
            }

            $data = array_map(fn($product) => $product->toArray(), $products);

            return $this->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => is_array($products) ? count($products) : $products['total'] ?? count($products),
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to retrieve products',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProduct($id);

            return $this->json([
                'success' => true,
                'data' => $product->toArray()
            ]);

        } catch (NotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to retrieve product',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON data',
                    'message' => 'The request body contains invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $productDTO = ProductDTO::fromArray($data);
            $errors = $productDTO->validate();

            if (!empty($errors)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $product = $this->productService->createProduct($productDTO);

            return new JsonResponse([
                'success' => true,
                'data' => $product->toArray(),
                'message' => 'Product created successfully'
            ], Response::HTTP_CREATED);

        } catch (NotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Resource not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        } catch (ValidationException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Validation error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to create product',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON data',
                    'message' => 'The request body contains invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $productDTO = ProductDTO::fromArray($data);
            $product = $this->productService->updateProduct($id, $productDTO);

            return $this->json([
                'success' => true,
                'data' => $product->toArray(),
                'message' => 'Product updated successfully'
            ]);

        } catch (NotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        } catch (ValidationException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Validation error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to update product',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->productService->deleteProduct($id);

            return $this->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (NotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        } catch (ValidationException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Validation error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to delete product',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/stock', methods: ['PATCH'])]
    public function updateStock(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON data',
                    'message' => 'The request body contains invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $quantity = $data['quantity'] ?? 0;
            $reason = $data['reason'] ?? 'Manual adjustment';

            if (!is_int($quantity)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid quantity',
                    'message' => 'Quantity must be an integer'
                ], Response::HTTP_BAD_REQUEST);
            }

            $product = $this->productService->updateStock($id, $quantity, $reason);

            return $this->json([
                'success' => true,
                'data' => $product->toArray(),
                'message' => 'Stock updated successfully'
            ]);

        } catch (NotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to update stock',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/price', methods: ['PATCH'])]
    public function updatePrice(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON data',
                    'message' => 'The request body contains invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $newPrice = $data['price'] ?? null;

            if (!is_numeric($newPrice) || $newPrice <= 0) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid price',
                    'message' => 'Price must be a positive number'
                ], Response::HTTP_BAD_REQUEST);
            }

            $product = $this->productService->adjustPrice($id, (float) $newPrice);

            return $this->json([
                'success' => true,
                'data' => $product->toArray(),
                'message' => 'Price updated successfully'
            ]);

        } catch (NotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to update price',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/duplicate', methods: ['POST'])]
    public function duplicate(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON data',
                    'message' => 'The request body contains invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $newSku = $data['sku'] ?? null;
            $newName = $data['name'] ?? null;

            if (empty($newSku) || empty($newName)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Missing required fields',
                    'message' => 'Both SKU and name are required for duplication'
                ], Response::HTTP_BAD_REQUEST);
            }

            $product = $this->productService->duplicateProduct($id, $newSku, $newName);

            return new JsonResponse([
                'success' => true,
                'data' => $product->toArray(),
                'message' => 'Product duplicated successfully'
            ], Response::HTTP_CREATED);

        } catch (NotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);

        } catch (ValidationException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Validation error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to duplicate product',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/statistics', methods: ['GET'])]
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->productService->getProductStatistics();

            return $this->json([
                'success' => true,
                'data' => [
                    'total' => count($stats['total']),
                    'active' => count($stats['active']),
                    'lowStock' => count($stats['lowStock']),
                    'outOfStock' => count($stats['outOfStock']),
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to retrieve statistics',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
