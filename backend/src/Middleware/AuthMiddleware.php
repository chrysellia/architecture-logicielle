<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Application\Service\AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthMiddleware
{
    private AuthService $authService;
    private array $protectedRoutes;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->protectedRoutes = [
            '/api/auth/me',
            '/api/invoices',
            '/api/orders',
            '/api/customers',
            '/api/products',
            '/api/stock-movements'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Ne pas protéger les routes publiques
        if ($this->isPublicRoute($path)) {
            return;
        }

        // Vérifier si la route est protégée
        if ($this->isProtectedRoute($path)) {
            $token = $this->extractTokenFromRequest($request);

            if (!$token) {
                $event->setResponse(new JsonResponse([
                    'success' => false,
                    'message' => 'Token required'
                ], Response::HTTP_UNAUTHORIZED));
                return;
            }

            $payload = $this->authService->validateToken($token);

            if (!$payload) {
                $event->setResponse(new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ], Response::HTTP_UNAUTHORIZED));
                return;
            }

            // Ajouter les informations de l'utilisateur à la requête
            $request->attributes->set('user_id', $payload['user_id']);
            $request->attributes->set('user_email', $payload['email']);
        }
    }

    private function isPublicRoute(string $path): bool
    {
        $publicRoutes = [
            '/api/auth/login',
            '/api/auth/register',
            '/api/invoices/download',
            '/health',
            '/api/orders',
            '/api/customers',
            '/api/products',
            '/api/invoices',
            '/api/stock-movements',
            '/api/dashboard/stats'
        ];

        // Vérifier si c'est une route publique exacte ou partiellement
        foreach ($publicRoutes as $publicRoute) {
            if (str_starts_with($path, $publicRoute)) {
                return true;
            }
        }

        return false;
    }

    private function isProtectedRoute(string $path): bool
    {
        // Les routes d'authentification ne sont pas protégées
        if (str_starts_with($path, '/api/auth/')) {
            return false;
        }

        foreach ($this->protectedRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }
        return false;
    }

    private function extractTokenFromRequest(Request $request): ?string
    {
        // Extraire le token du header Authorization
        $authHeader = $request->headers->get('Authorization');
        
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return null;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
