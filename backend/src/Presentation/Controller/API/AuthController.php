<?php

declare(strict_types=1);

namespace App\Presentation\Controller\API;

use App\Application\Service\AuthService;
use App\Application\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private AuthService $authService;
    private EmailService $emailService;

    public function __construct(AuthService $authService, EmailService $emailService)
    {
        $this->authService = $authService;
        $this->emailService = $emailService;
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

    #[Route('/api/auth/forgot-password', name: 'api_auth_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        try {
            $resetToken = $this->authService->requestPasswordReset($data['email']);
            
            if ($resetToken) {
                $emailSent = $this->emailService->sendPasswordResetEmail($data['email'], $resetToken);
                
                if ($emailSent) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Si cet email existe dans notre base de données, vous recevrez un lien de réinitialisation.'
                    ]);
                } else {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Erreur lors de l\'envoi de l\'email'
                    ], 500);
                }
            } else {
                // Pour des raisons de sécurité, on retourne toujours le même message
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Si cet email existe dans notre base de données, vous recevrez un lien de réinitialisation.'
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    #[Route('/api/auth/reset-password', name: 'api_auth_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token']) || !isset($data['password'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Token and password are required'
            ], 400);
        }

        try {
            $success = $this->authService->resetPassword($data['token'], $data['password']);
            
            if ($success) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Mot de passe réinitialisé avec succès'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ], 400);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    #[Route('/api/auth/validate-reset-token', name: 'api_auth_validate_reset_token', methods: ['POST'])]
    public function validateResetToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Token is required'
            ], 400);
        }

        try {
            $tokenData = $this->authService->validateResetToken($data['token']);
            
            if ($tokenData) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Token valide',
                    'data' => [
                        'email' => $tokenData['email']
                    ]
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ], 400);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }
}
