# Mini ERP - Système de Gestion de Vente

## Contexte du projet

Une entreprise commerciale souhaite développer une application pour gérer :
- Produits
- Stock  
- Clients
- Commandes
- Factures
- Paiements
- Utilisateurs (Admin, Vendeur)

## Architecture

Ce projet utilise **Clean Architecture** avec une séparation frontend/backend :
- **Backend** : API REST en Symfony 6.4 avec PHP 8.2+
- **Frontend** : Application React 18 avec TypeScript
- **Base de données** : PostgreSQL avec Doctrine ORM
- **Cache** : Redis
- **Conteneurisation** : Docker & Docker Compose

Principes appliqués :
- Modulaire
- Évolutive  
- Sécurisée
- Maintenable
- Déployable en production
- Principes SOLID
- Clean Architecture

## Structure du projet

```
mini-erp/
├── backend/               # API REST Symfony
│   ├── src/
│   │   ├── domain/        # Entités et règles métier
│   │   ├── application/   # Use cases et services
│   │   ├── infrastructure/ # Base de données, repositories
│   │   ├── presentation/  # Contrôleurs API
│   │   └── shared/        # Utilitaires partagés
│   ├── prisma/           # Schéma de base de données
│   └── tests/            # Tests backend
├── frontend/              # Application React
│   ├── src/
│   │   ├── components/    # Composants UI réutilisables
│   │   ├── pages/         # Pages de l'application
│   │   ├── hooks/         # Hooks personnalisés
│   │   ├── services/      # Services API
│   │   ├── store/         # État global (Redux/Zustand)
│   │   ├── types/         # Types TypeScript
│   │   └── utils/         # Utilitaires
│   └── public/            # Fichiers statiques
├── docs/                  # Documentation
└── docker-compose.yml     # Configuration Docker
```

## Technologies

### Backend
- **Node.js 20+** - Runtime JavaScript
- **TypeScript** - Typage statique
- **Express.js** - Framework web
- **Prisma** - ORM et gestion de schéma
- **PostgreSQL** - Base de données
- **JWT** - Authentification
- **Jest** - Tests unitaires
- **Zod** - Validation des données

### Frontend
- **React 18** - Bibliothèque UI
- **TypeScript** - Typage statique
- **Vite** - Build tool et dev server
- **Tailwind CSS** - Framework CSS
- **React Router** - Routage
- **TanStack Query** - Gestion des requêtes API
- **Zustand** - État global
- **React Hook Form** - Formulaires
- **Lucide React** - Icônes

## Bonus implémentés

-  Architecture Hexagonale
-  Event Driven Architecture
-  CQRS (Command Query Responsibility Segregation)
-  Event Sourcing

## Démarrage rapide

```bash
# Cloner le projet
git clone [repository-url]
cd mini-erp

# Démarrer avec Docker
docker-compose up

# Ou lancer localement
# Backend
cd backend
npm install
npm run dev

# Frontend (dans un autre terminal)
cd frontend
npm install
npm run dev
```

## URLs de développement
- Frontend : http://localhost:5173
- API Backend : http://localhost:3000
- Base de données : localhost:5432