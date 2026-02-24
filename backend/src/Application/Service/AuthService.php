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
}
