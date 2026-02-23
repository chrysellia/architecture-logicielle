<?php

namespace App\Presentation\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SimpleCustomerController extends AbstractController
{
    private string $storageFile;

    public function __construct()
    {
        $this->storageFile = '/tmp/customers.json';
    }

    private function ensureStorageInitialized(): void
    {
        // Ne réinitialiser que si le fichier n'existe vraiment pas ou est vide
        if (!file_exists($this->storageFile) || filesize($this->storageFile) === 0) {
            $initialCustomers = [
                [
                    'id' => 1,
                    'firstName' => 'Jean',
                    'lastName' => 'Dupont',
                    'email' => 'jean.dupont@email.com',
                    'phone' => '06 12 34 56 78',
                    'address' => '123 Rue de la République',
                    'city' => 'Paris',
                    'postalCode' => '75001',
                    'country' => 'France',
                    'createdAt' => '2024-01-15T10:00:00Z'
                ],
                [
                    'id' => 2,
                    'firstName' => 'Marie',
                    'lastName' => 'Martin',
                    'email' => 'marie.martin@email.com',
                    'phone' => '06 23 45 67 89',
                    'address' => '45 Avenue des Champs-Élysées',
                    'city' => 'Paris',
                    'postalCode' => '75008',
                    'country' => 'France',
                    'createdAt' => '2024-01-20T14:30:00Z'
                ],
                [
                    'id' => 3,
                    'firstName' => 'Pierre',
                    'lastName' => 'Bernard',
                    'email' => 'pierre.bernard@email.com',
                    'phone' => '06 34 56 78 90',
                    'address' => '78 Boulevard Saint-Germain',
                    'city' => 'Paris',
                    'postalCode' => '75005',
                    'country' => 'France',
                    'createdAt' => '2024-02-01T09:15:00Z'
                ]
            ];
            file_put_contents($this->storageFile, json_encode($initialCustomers));
        }
    }

    private function getCustomers(): array
    {
        $this->ensureStorageInitialized();
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?: [];
    }

    private function saveCustomers(array $customers): void
    {
        $this->ensureStorageInitialized();
        file_put_contents($this->storageFile, json_encode($customers));
    }

    private function getNextId(): int
    {
        $customers = $this->getCustomers();
        $maxId = 0;
        foreach ($customers as $customer) {
            if ($customer['id'] > $maxId) {
                $maxId = $customer['id'];
            }
        }
        return $maxId + 1;
    }

    #[Route('/customers', methods: ['GET'])]
    public function getCustomersAction(): JsonResponse
    {
        $this->ensureStorageInitialized();
        $customers = $this->getCustomers();
        
        return new JsonResponse([
            'success' => true,
            'data' => $customers,
            'message' => 'Customers retrieved successfully'
        ]);
    }

    #[Route('/customers/{id}', methods: ['GET'])]
    public function getCustomerAction(int $id): JsonResponse
    {
        $customers = $this->getCustomers();
        foreach ($customers as $customer) {
            if ($customer['id'] === $id) {
                return new JsonResponse([
                    'success' => true,
                    'data' => $customer,
                    'message' => 'Customer retrieved successfully'
                ]);
            }
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Customer not found'
        ], 404);
    }

    #[Route('/customers', methods: ['POST'])]
    public function createCustomerAction(Request $request): JsonResponse
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
        if (!isset($data['firstName']) || !isset($data['lastName']) || !isset($data['email'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: firstName, lastName, email'
            ], 400);
        }

        $customer = [
            'id' => $this->getNextId(),
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'postalCode' => $data['postalCode'] ?? '',
            'country' => $data['country'] ?? '',
            'createdAt' => date('c')
        ];

        $customers = $this->getCustomers();
        $customers[] = $customer;
        $this->saveCustomers($customers);
        
        return new JsonResponse([
            'success' => true,
            'data' => $customer,
            'message' => 'Customer created successfully'
        ], 201);
    }

    #[Route('/customers/{id}', methods: ['PUT'])]
    public function updateCustomerAction(Request $request, int $id): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], 400);
        }

        $customers = $this->getCustomers();
        foreach ($customers as &$customer) {
            if ($customer['id'] === $id) {
                if (isset($data['firstName'])) $customer['firstName'] = $data['firstName'];
                if (isset($data['lastName'])) $customer['lastName'] = $data['lastName'];
                if (isset($data['email'])) $customer['email'] = $data['email'];
                if (isset($data['phone'])) $customer['phone'] = $data['phone'];
                if (isset($data['address'])) $customer['address'] = $data['address'];
                if (isset($data['city'])) $customer['city'] = $data['city'];
                if (isset($data['postalCode'])) $customer['postalCode'] = $data['postalCode'];
                if (isset($data['country'])) $customer['country'] = $data['country'];
                
                $this->saveCustomers($customers);
                
                return new JsonResponse([
                    'success' => true,
                    'data' => $customer,
                    'message' => 'Customer updated successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Customer not found'
        ], 404);
    }

    #[Route('/customers/{id}', methods: ['DELETE'])]
    public function deleteCustomerAction(int $id): JsonResponse
    {
        $customers = $this->getCustomers();
        foreach ($customers as $key => $customer) {
            if ($customer['id'] === $id) {
                unset($customers[$key]);
                $customers = array_values($customers);
                $this->saveCustomers($customers);
                
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Customer deleted successfully'
                ]);
            }
        }
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Customer not found'
        ], 404);
    }
}
