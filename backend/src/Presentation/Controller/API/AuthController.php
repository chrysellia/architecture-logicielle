<?php

declare(strict_types=1);

namespace App\Presentation\Controller\API;

use App\Application\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        // Debug
        error_log("Raw content: " . $content);
        error_log("Decoded data: " . print_r($data, true));

        if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: email, password, name',
                'debug' => [
                    'content' => $content,
                    'data' => $data
                ]
            ], 400);
        }

        try {
            $user = $this->authService->register($data['email'], $data['password'], $data['name']);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ]
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: email, password'
            ], 400);
        }

        try {
            $result = $this->authService->login($data['email'], $data['password']);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']['id'],
                        'email' => $result['user']['email'],
                        'name' => $result['user']['name']
                    ],
                    'token' => $result['token']
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    #[Route('/api/auth/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        // Cette route sera protégée par le middleware
        return new JsonResponse([
            'success' => true,
            'data' => [
                'message' => 'Protected route accessed successfully'
            ]
        ]);
    }
}
