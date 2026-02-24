<?php

declare(strict_types=1);

namespace App\Application\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private string $jwtSecret;
    private string $usersFile;

    public function __construct(string $jwtSecret = null)
    {
        $this->jwtSecret = $jwtSecret ?? $_ENV['JWT_SECRET_KEY'] ?? 'fallback-secret-key';
        $this->usersFile = '/tmp/users.json';
        $this->initializeUsersFile();
    }

    private function initializeUsersFile(): void
    {
        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, json_encode([]));
        }
    }

    private function getUsers(): array
    {
        if (!file_exists($this->usersFile)) {
            return [];
        }

        $users = json_decode(file_get_contents($this->usersFile), true);
        return is_array($users) ? $users : [];
    }

    private function saveUsers(array $users): void
    {
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function register(string $email, string $password, string $name): array
    {
        $users = $this->getUsers();

        // Vérifier si l'email existe déjà
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                throw new \Exception('Email already exists');
            }
        }

        // Créer le nouvel utilisateur
        $user = [
            'id' => count($users) + 1,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $users[] = $user;
        $this->saveUsers($users);

        // Retourner l'utilisateur sans le mot de passe
        unset($user['password']);
        return $user;
    }

    public function login(string $email, string $password): array
    {
        $users = $this->getUsers();

        // Trouver l'utilisateur
        $userFound = null;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $userFound = $user;
                break;
            }
        }

        if (!$userFound) {
            throw new \Exception('Invalid credentials');
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $userFound['password'])) {
            throw new \Exception('Invalid credentials');
        }

        // Générer le token JWT
        $payload = [
            'user_id' => $userFound['id'],
            'email' => $userFound['email'],
            'exp' => time() + 3600, // Expire dans 1 heure
            'iat' => time()
        ];

        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        // Retourner l'utilisateur sans le mot de passe
        unset($userFound['password']);
        
        return [
            'user' => $userFound,
            'token' => $token
        ];
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUserById(int $userId): ?array
    {
        $users = $this->getUsers();

        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                unset($user['password']);
                return $user;
            }
        }

        return null;
    }

    public function requestPasswordReset(string $email): ?string
    {
        $users = $this->getUsers();

        // Trouver l'utilisateur par email
        $user = null;
        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            return null; // Ne pas révéler si l'email existe ou non
        }

        // Générer un token de réinitialisation
        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = time() + 3600; // Expire dans 1 heure

        // Sauvegarder le token
        $resetTokensFile = '/tmp/password_reset_tokens.json';
        $tokens = [];
        
        if (file_exists($resetTokensFile)) {
            $tokens = json_decode(file_get_contents($resetTokensFile), true) ?: [];
        }

        // Nettoyer les tokens expirés
        $tokens = array_filter($tokens, function($token) {
            return $token['expires_at'] > time();
        });

        $tokens[$resetToken] = [
            'user_id' => $user['id'],
            'email' => $email,
            'expires_at' => $expiresAt,
            'created_at' => time()
        ];

        file_put_contents($resetTokensFile, json_encode($tokens, JSON_PRETTY_PRINT));

        return $resetToken;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $resetTokensFile = '/tmp/password_reset_tokens.json';
        
        if (!file_exists($resetTokensFile)) {
            return false;
        }

        $tokens = json_decode(file_get_contents($resetTokensFile), true) ?: [];
        
        if (!isset($tokens[$token])) {
            return false;
        }

        $tokenData = $tokens[$token];
        
        // Vérifier si le token n'est pas expiré
        if ($tokenData['expires_at'] < time()) {
            return false;
        }

        // Mettre à jour le mot de passe de l'utilisateur
        $users = $this->getUsers();
        $userId = $tokenData['user_id'];
        
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $user['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }

        $this->saveUsers($users);

        // Supprimer le token utilisé
        unset($tokens[$token]);
        file_put_contents($resetTokensFile, json_encode($tokens, JSON_PRETTY_PRINT));

        return true;
    }

    public function validateResetToken(string $token): ?array
    {
        $resetTokensFile = '/tmp/password_reset_tokens.json';
        
        if (!file_exists($resetTokensFile)) {
            return null;
        }

        $tokens = json_decode(file_get_contents($resetTokensFile), true) ?: [];
        
        if (!isset($tokens[$token])) {
            return null;
        }

        $tokenData = $tokens[$token];
        
        // Vérifier si le token n'est pas expiré
        if ($tokenData['expires_at'] < time()) {
            return null;
        }

        return $tokenData;
    }
}
