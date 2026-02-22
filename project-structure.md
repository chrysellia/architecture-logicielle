# Structure du Projet Mini ERP

## Architecture Globale

```
mini-erp/
├── backend/                    # API Symfony (Clean Architecture)
│   ├── src/
│   │   ├── Domain/             # Couche Domaine
│   │   │   ├── Entity/         # Entités métier
│   │   │   │   ├── User.php
│   │   │   │   ├── Customer.php
│   │   │   │   ├── Product.php
│   │   │   │   ├── Category.php
│   │   │   │   ├── Order.php
│   │   │   │   ├── OrderItem.php
│   │   │   │   ├── Invoice.php
│   │   │   │   ├── Payment.php
│   │   │   │   └── StockMovement.php
│   │   │   ├── Repository/     # Interfaces des repositories
│   │   │   │   ├── UserRepositoryInterface.php
│   │   │   │   ├── CustomerRepositoryInterface.php
│   │   │   │   ├── ProductRepositoryInterface.php
│   │   │   │   ├── OrderRepositoryInterface.php
│   │   │   │   ├── InvoiceRepositoryInterface.php
│   │   │   │   └── StockRepositoryInterface.php
│   │   │   ├── Service/        # Services de domaine
│   │   │   │   ├── OrderValidationService.php
│   │   │   │   ├── StockManagementService.php
│   │   │   │   └── PricingService.php
│   │   │   └── ValueObject/    # Objets valeur
│   │   │       ├── Money.php
│   │   │       ├── Email.php
│   │   │       └── Address.php
│   │   ├── Application/        # Couche Application
│   │   │   ├── UseCase/        # Cas d'utilisation
│   │   │   │   ├── Order/CreateOrderUseCase.php
│   │   │   │   ├── Order/UpdateOrderStatusUseCase.php
│   │   │   │   ├── Product/CreateProductUseCase.php
│   │   │   │   ├── Stock/UpdateStockUseCase.php
│   │   │   │   └── Invoice/GenerateInvoiceUseCase.php
│   │   │   ├── Service/        # Services d'application
│   │   │   │   ├── OrderService.php
│   │   │   │   ├── ProductService.php
│   │   │   │   ├── CustomerService.php
│   │   │   │   ├── InvoiceService.php
│   │   │   │   ├── StockService.php
│   │   │   │   └── PaymentService.php
│   │   │   └── DTO/           # Data Transfer Objects
│   │   │       ├── OrderDTO.php
│   │   │       ├── ProductDTO.php
│   │   │       ├── CustomerDTO.php
│   │   │       └── InvoiceDTO.php
│   │   ├── Infrastructure/      # Couche Infrastructure
│   │   │   ├── Persistence/    # Implémentation repositories
│   │   │   │   ├── Doctrine/Repository/
│   │   │   │   │   ├── UserRepository.php
│   │   │   │   │   ├── CustomerRepository.php
│   │   │   │   │   ├── ProductRepository.php
│   │   │   │   │   ├── OrderRepository.php
│   │   │   │   │   ├── InvoiceRepository.php
│   │   │   │   │   └── StockRepository.php
│   │   │   │   └── ORM/Mapping/
│   │   │   │       └── (annotations XML/YAML)
│   │   │   ├── Security/       # Sécurité
│   │   │   │   ├── JWT/JWTManager.php
│   │   │   │   └── UserProvider.php
│   │   │   ├── Messaging/      # Events/Messages
│   │   │   │   ├── Event/OrderCreatedEvent.php
│   │   │   │   ├── Event/LowStockEvent.php
│   │   │   │   └── Handler/
│   │   │   └── External/       # Services externes
│   │   │       ├── PaymentGateway/
│   │   │       └── EmailService/
│   │   ├── Presentation/       # Couche Présentation
│   │   │   ├── Controller/     # Controllers API
│   │   │   │   ├── API/
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   ├── OrderController.php
│   │   │   │   │   ├── CustomerController.php
│   │   │   │   │   ├── InvoiceController.php
│   │   │   │   │   ├── StockController.php
│   │   │   │   │   └── AuthController.php
│   │   │   │   └── Admin/
│   │   │   │       └── DashboardController.php
│   │   │   ├── Request/        # Request DTOs
│   │   │   │   ├── CreateOrderRequest.php
│   │   │   │   ├── UpdateProductRequest.php
│   │   │   │   └── LoginRequest.php
│   │   │   └── Response/       # Response DTOs
│   │   │       ├── OrderResponse.php
│   │   │       ├── ProductResponse.php
│   │   │       └── ErrorResponse.php
│   │   └── Shared/            # Éléments partagés
│   │       ├── Exception/     # Exceptions personnalisées
│   │       │   ├── DomainException.php
│   │       │   ├── ValidationException.php
│   │       │   └── NotFoundException.php
│   │       ├── Event/         # Events de domaine
│   │       └── Util/          # Utilitaires
│   ├── config/                # Configuration Symfony
│   │   ├── packages/
│   │   ├── routes/
│   │   └── services.yaml
│   ├── public/                # Point d'entrée web
│   ├── tests/                 # Tests
│   ├── migrations/            # Migrations Doctrine
│   └── templates/             # Templates (si besoin)
├── frontend/                  # Application React
│   ├── public/                # Fichiers statiques
│   ├── src/
│   │   ├── components/        # Composants réutilisables
│   │   │   ├── ui/           # Composants UI de base
│   │   │   │   ├── Button.tsx
│   │   │   │   ├── Input.tsx
│   │   │   │   ├── Modal.tsx
│   │   │   │   ├── Table.tsx
│   │   │   │   └── index.ts
│   │   │   ├── layout/        # Composants de layout
│   │   │   │   ├── Header.tsx
│   │   │   │   ├── Sidebar.tsx
│   │   │   │   └── Layout.tsx
│   │   │   └── forms/         # Composants de formulaire
│   │   │       ├── ProductForm.tsx
│   │   │       ├── OrderForm.tsx
│   │   │       └── CustomerForm.tsx
│   │   ├── pages/             # Pages de l'application
│   │   │   ├── Dashboard/
│   │   │   │   └── DashboardPage.tsx
│   │   │   ├── Products/
│   │   │   │   ├── ProductListPage.tsx
│   │   │   │   ├── ProductDetailPage.tsx
│   │   │   │   └── ProductFormPage.tsx
│   │   │   ├── Orders/
│   │   │   │   ├── OrderListPage.tsx
│   │   │   │   ├── OrderDetailPage.tsx
│   │   │   │   └── CreateOrderPage.tsx
│   │   │   ├── Customers/
│   │   │   │   ├── CustomerListPage.tsx
│   │   │   │   └── CustomerFormPage.tsx
│   │   │   ├── Stock/
│   │   │   │   └── StockPage.tsx
│   │   │   ├── Invoices/
│   │   │   │   ├── InvoiceListPage.tsx
│   │   │   │   └── InvoiceDetailPage.tsx
│   │   │   └── Auth/
│   │   │       ├── LoginPage.tsx
│   │   │       └── RegisterPage.tsx
│   │   ├── hooks/             # Hooks personnalisés
│   │   │   ├── useAuth.ts
│   │   │   ├── useProducts.ts
│   │   │   ├── useOrders.ts
│   │   │   └── useStock.ts
│   │   ├── services/          # Services API
│   │   │   ├── api.ts         # Configuration axios
│   │   │   ├── authService.ts
│   │   │   ├── productService.ts
│   │   │   ├── orderService.ts
│   │   │   ├── customerService.ts
│   │   │   ├── invoiceService.ts
│   │   │   └── stockService.ts
│   │   ├── store/             # État global (Zustand)
│   │   │   ├── authStore.ts
│   │   │   ├── productStore.ts
│   │   │   ├── orderStore.ts
│   │   │   └── index.ts
│   │   ├── types/             # Types TypeScript
│   │   │   ├── auth.ts
│   │   │   ├── product.ts
│   │   │   ├── order.ts
│   │   │   ├── customer.ts
│   │   │   ├── invoice.ts
│   │   │   └── api.ts
│   │   ├── utils/             # Utilitaires
│   │   │   ├── constants.ts
│   │   │   ├── formatters.ts
│   │   │   ├── validators.ts
│   │   │   └── cn.ts
│   │   ├── styles/            # Styles CSS
│   │   │   └── globals.css
│   │   ├── App.tsx            # Composant racine
│   │   ├── main.tsx           # Point d'entrée
│   │   └── vite-env.d.ts      # Types Vite
│   ├── package.json
│   ├── vite.config.ts
│   ├── tailwind.config.js
│   ├── tsconfig.json
│   └── postcss.config.js
├── docs/                      # Documentation
│   ├── architecture-analysis.md
│   ├── uml-diagrams.md
│   ├── api-documentation.md
│   └── deployment-guide.md
├── docker-compose.yml         # Configuration Docker
├── docker-compose.prod.yml    # Configuration production
├── .env.example               # Variables d'environnement exemple
└── README.md                  # Documentation du projet
```

## Technologies Utilisées

### Backend Symfony
- **PHP 8.2+**
- **Symfony 6.4** (Framework)
- **Doctrine ORM** (Base de données)
- **PostgreSQL** (SGBD)
- **JWT Authentication** (Sécurité)
- **API Platform** (API REST)
- **Symfony Messenger** (Events/Queue)

### Frontend React
- **React 18** (UI Framework)
- **TypeScript** (Typage)
- **Vite** (Build tool)
- **Tailwind CSS** (Styling)
- **React Router** (Routing)
- **TanStack Query** (Gestion API)
- **Zustand** (État global)
- **React Hook Form** (Formulaires)
- **Lucide React** (Icônes)

### DevOps & Déploiement
- **Docker & Docker Compose**
- **Nginx** (Reverse proxy)
- **PostgreSQL** (Base de données)
- **Redis** (Cache & sessions)

## Principes d'Architecture Appliqués

### Clean Architecture
- **Domain Layer** : Logique métier pure
- **Application Layer** : Cas d'utilisation
- **Infrastructure Layer** : Implémentations techniques
- **Presentation Layer** : API et controllers

### Principes SOLID
- **Single Responsibility** : Une classe = une responsabilité
- **Open/Closed** : Ouvert à l'extension, fermé à la modification
- **Liskov Substitution** : Sous-types remplaçables
- **Interface Segregation** : Interfaces spécifiques
- **Dependency Inversion** : Dépendances inversées

### Patterns Utilisés
- **Repository Pattern** : Accès aux données
- **Factory Pattern** : Création d'objets
- **Observer Pattern** : Events métier
- **DTO Pattern** : Transfert de données
- **Service Layer** : Logique métier
