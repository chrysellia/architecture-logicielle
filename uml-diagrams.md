# Diagrammes UML - Mini ERP

## 1. Diagramme des Cas d'Utilisation (Use Case Diagram)

```mermaid
graph TD
    A[Admin] --> B[Gestion des Produits]
    A --> C[Gestion des Stocks]
    A --> D[Gestion des Clients]
    A --> E[Gestion des Commandes]
    A --> F[Gestion des Factures]
    A --> G[Gestion des Utilisateurs]
    A --> H[Rapports et Statistiques]
    
    I[Vendeur] --> B
    I --> C
    I --> D
    I --> E
    I --> F
    I --> H
    
    J[Client] --> K[Consulter les Produits]
    J --> L[Passer Commande]
    J --> M[Suivre Commande]
    J --> N[Voir Factures]
    J --> O[Gérer Compte]
    
    P[System] --> Q[Authentification]
    P --> R[Gestion des Paiements]
    P --> S[Notifications]
    P --> T[Export de Données]
```

## 2. Diagramme de Classes (Class Diagram)

```mermaid
classDiagram
    class User {
        +int id
        +string email
        +string password
        +string firstName
        +string lastName
        +Role role
        +datetime createdAt
        +datetime updatedAt
        +authenticate()
        +hasPermission()
    }
    
    class Role {
        +int id
        +string name
        +string description
        +json permissions
    }
    
    class Customer {
        +int id
        +string email
        +string firstName
        +string lastName
        +string phone
        +string address
        +datetime createdAt
        +getOrders()
        +getTotalSpent()
    }
    
    class Product {
        +int id
        +string name
        +string description
        +decimal price
        +string sku
        +int stockQuantity
        +Category category
        +boolean isActive
        +updateStock()
        +isAvailable()
    }
    
    class Category {
        +int id
        +string name
        +string description
        +Category parent
        +getProducts()
    }
    
    class Order {
        +int id
        +Customer customer
        +OrderStatus status
        +datetime orderDate
        +decimal totalAmount
        +string shippingAddress
        +addItem()
        +calculateTotal()
        +updateStatus()
    }
    
    class OrderItem {
        +int id
        +Order order
        +Product product
        +int quantity
        +decimal unitPrice
        +decimal totalPrice
    }
    
    class Invoice {
        +int id
        +Order order
        +string invoiceNumber
        +datetime issueDate
        +datetime dueDate
        +decimal totalAmount
        +InvoiceStatus status
        +generatePDF()
        +calculateTaxes()
    }
    
    class Payment {
        +int id
        +Invoice invoice
        +decimal amount
        +PaymentMethod method
        +PaymentStatus status
        +datetime paymentDate
        +string transactionId
        +processPayment()
        +refund()
    }
    
    class StockMovement {
        +int id
        +Product product
        +MovementType type
        +int quantity
        +string reason
        +datetime movementDate
        +User user
    }
    
    class OrderStatus {
        <<enumeration>>
        PENDING
        CONFIRMED
        PROCESSING
        SHIPPED
        DELIVERED
        CANCELLED
    }
    
    class PaymentStatus {
        <<enumeration>>
        PENDING
        COMPLETED
        FAILED
        REFUNDED
    }
    
    class MovementType {
        <<enumeration>>
        IN
        OUT
        ADJUSTMENT
    }
    
    User ||--o{ Role : has
    Customer ||--o{ Order : places
    Order ||--o{ OrderItem : contains
    Order ||--|| Invoice : generates
    Invoice ||--o{ Payment : receives
    Product ||--o{ OrderItem : ordered_in
    Product ||--o{ StockMovement : tracked_in
    Product }|--|| Category : belongs_to
    Category ||--o{ Category : parent_child
    User ||--o{ StockMovement : performs
```

## 3. Diagramme de Séquence - Processus de Commande

```mermaid
sequenceDiagram
    participant C as Client
    participant F as Frontend React
    participant API as API Symfony
    participant OS as OrderService
    participant PS as ProductService
    participant SS as StockService
    participant DB as Database
    
    C->>F: Consulte produits
    F->>API: GET /api/products
    API->>PS: getAvailableProducts()
    PS->>DB: SELECT * FROM products
    DB-->>PS: Product list
    PS-->>API: Products with stock
    API-->>F: JSON response
    F-->>C: Affiche catalogue
    
    C->>F: Ajoute au panier
    F->>F: Mise à jour panier local
    
    C->>F: Valide commande
    F->>API: POST /api/orders
    API->>OS: createOrder(orderData)
    
    OS->>PS: validateProducts(items)
    PS->>DB: Check stock availability
    DB-->>PS: Stock levels
    PS-->>OS: Validation result
    
    OS->>SS: reserveStock(items)
    SS->>DB: UPDATE stock quantities
    DB-->>SS: Updated stock
    SS-->>OS: Stock reserved
    
    OS->>DB: INSERT order
    OS->>DB: INSERT order_items
    DB-->>OS: Order created
    
    OS-->>API: Order confirmation
    API-->>F: Order response
    F-->>C: Confirmation commande
```

## 4. Diagramme de Séquence - Gestion des Stocks

```mermaid
sequenceDiagram
    participant A as Admin
    participant F as Frontend
    participant API as API Symfony
    participant SS as StockService
    participant PS as ProductService
    participant DB as Database
    
    A->>F: Met à jour stock
    F->>API: PUT /api/stock/movements
    API->>SS: recordMovement(movementData)
    
    SS->>PS: getProduct(productId)
    PS->>DB: SELECT * FROM products WHERE id
    DB-->>PS: Product details
    PS-->>SS: Product info
    
    SS->>DB: INSERT stock_movement
    SS->>DB: UPDATE product stock
    DB-->>SS: Movement recorded
    
    alt Stock bas
        SS->>SS: checkLowStockThreshold()
        SS->>API: Trigger low stock alert
        API-->>F: Notification
        F-->>A: Alerte stock bas
    end
    
    SS-->>API: Movement confirmation
    API-->>F: Success response
    F-->>A: Confirmation mise à jour
```

## 5. Diagramme de Composants - Architecture Symfony

```mermaid
graph TB
    subgraph "Presentation Layer"
        A[ProductController]
        B[OrderController]
        C[CustomerController]
        D[InvoiceController]
        E[StockController]
    end
    
    subgraph "Application Layer"
        F[ProductService]
        G[OrderService]
        H[CustomerService]
        I[InvoiceService]
        J[StockService]
        K[PaymentService]
    end
    
    subgraph "Domain Layer"
        L[Product Entity]
        M[Order Entity]
        N[Customer Entity]
        O[Invoice Entity]
        P[StockMovement Entity]
    end
    
    subgraph "Infrastructure Layer"
        Q[ProductRepository]
        R[OrderRepository]
        S[CustomerRepository]
        T[InvoiceRepository]
        U[StockRepository]
        V[Database]
    end
    
    A --> F
    B --> G
    C --> H
    D --> I
    E --> J
    
    F --> L
    G --> M
    H --> N
    I --> O
    J --> P
    
    F --> Q
    G --> R
    H --> S
    I --> T
    J --> U
    
    Q --> V
    R --> V
    S --> V
    T --> V
    U --> V
```
