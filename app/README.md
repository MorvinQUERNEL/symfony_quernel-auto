# 🚗 Quernel Auto - Plateforme d'Import/Export de Véhicules

<div align="center">
  <img src="public/images/logo_quernel_auto.png" alt="Quernel Auto Logo" width="300" height="auto">
  
  [![Symfony](https://img.shields.io/badge/Symfony-6.4+-000000?style=for-the-badge&logo=symfony)](https://symfony.com/)
  [![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
  [![Docker](https://img.shields.io/badge/Docker-20.10+-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
  [![Stripe](https://img.shields.io/badge/Stripe-008CDD?style=for-the-badge&logo=stripe&logoColor=white)](https://stripe.com/)
  
  **Plateforme moderne d'import/export de véhicules avec paiements sécurisés**
</div>

---

## 📋 Table des Matières

- [🎯 À Propos](#-à-propos)
- [✨ Fonctionnalités](#-fonctionnalités)
- [🏗️ Architecture](#️-architecture)
- [🚀 Installation](#-installation)
- [⚙️ Configuration](#️-configuration)
- [📱 Utilisation](#-utilisation)
- [🔧 Développement](#-développement)
- [📊 Structure du Projet](#-structure-du-projet)
- [🤝 Contribution](#-contribution)
- [📄 Licence](#-licence)

---

## 🎯 À Propos

**Quernel Auto** est une plateforme web moderne développée avec Symfony 6, spécialisée dans l'import et l'export de véhicules à l'international. Notre solution offre une expérience utilisateur fluide et sécurisée pour l'achat et la vente de véhicules de qualité.

### 🎨 Design Philosophie
- **Design moderne et sobre** avec une palette de couleurs professionnelle
- **Interface responsive** optimisée pour tous les appareils
- **Expérience utilisateur intuitive** avec navigation fluide
- **Accessibilité** conforme aux standards WCAG

---

## ✨ Fonctionnalités

### 🚗 Gestion des Véhicules
- **Catalogue complet** avec photos haute qualité
- **Recherche avancée** par marque, modèle, carburant, couleur
- **Filtres dynamiques** pour affiner les résultats
- **Carrousel interactif** sur la page d'accueil
- **Statuts en temps réel** (Disponible, Vendu, En cours, etc.)

### 👤 Gestion des Utilisateurs
- **Inscription/Connexion** sécurisée
- **Profils personnalisés** avec préférences
- **Gestion des commandes** et historique
- **Système de rôles** (Utilisateur, Admin, Super Admin)

### 💳 Paiements Sécurisés
- **Intégration Stripe** pour les paiements
- **Webhooks sécurisés** pour les notifications
- **Gestion des commandes** automatisée
- **Emails de confirmation** automatiques

### 📧 Communication
- **Formulaire de contact** avec validation
- **Notifications par email** (Symfony Mailer)
- **Messages flash** pour le feedback utilisateur
- **Support multilingue** (préparé)

### 🛠️ Administration
- **Interface d'administration** complète
- **Gestion des véhicules** (CRUD)
- **Gestion des utilisateurs** et permissions
- **Statistiques** et rapports
- **Commandes console** pour la maintenance

---

## 🏗️ Architecture

### 🎯 Stack Technique
```
Frontend:
├── Twig (Templates)
├── CSS3 avec variables personnalisées
├── JavaScript ES6+ (Vanilla)
├── Font Awesome (Icônes)
└── Responsive Design (Mobile First)

Backend:
├── Symfony 6.4+
├── PHP 8.1+
├── Doctrine ORM
├── Symfony Security
├── Symfony Mailer
└── Symfony Forms

Base de Données:
├── MySQL 8.0+
├── Migrations Doctrine
└── Fixtures pour les tests

Services Externes:
├── Stripe (Paiements)
├── Mailhog (Emails en dev)
└── Docker (Containerisation)
```

### 🏛️ Architecture MVC
```
src/
├── Controller/     # Contrôleurs métier
├── Entity/         # Entités Doctrine
├── Repository/     # Requêtes personnalisées
├── Form/          # Formulaires Symfony
├── Service/       # Services métier
└── EventListener/ # Écouteurs d'événements
```

---

## 🚀 Installation

### Prérequis
- **PHP** 8.1 ou supérieur
- **Composer** 2.0 ou supérieur
- **MySQL** 8.0 ou supérieur
- **Docker** (optionnel, pour le développement)

### 1. Cloner le Repository
```bash
git clone https://github.com/MorvinQUERNEL/symfony_quernel-auto.git
cd symfony_quernel-auto/app
```

### 2. Installer les Dépendances
```bash
composer install
```

### 3. Configuration de l'Environnement
```bash
# Copier le fichier d'environnement
cp .env .env.local

# Configurer la base de données dans .env.local
DATABASE_URL="mysql://username:password@127.0.0.1:3306/quernel_auto?serverVersion=8.0"
```

### 4. Base de Données
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures (optionnel)
php bin/console doctrine:fixtures:load
```

### 5. Configuration des Services
```bash
# Vider le cache
php bin/console cache:clear

# Créer un super admin
php bin/console app:make-super-admin admin@quernel-auto.com
```

### 6. Serveur de Développement
```bash
# Démarrer le serveur Symfony
symfony server:start

# Ou avec PHP
php -S localhost:8000 -t public/
```

---

## ⚙️ Configuration

### 🔐 Variables d'Environnement
```env
# Base de données
DATABASE_URL="mysql://username:password@127.0.0.1:3306/quernel_auto"

# Stripe (Paiements)
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Email (Mailhog en développement)
MAILER_DSN=smtp://localhost:1025

# Sécurité
APP_SECRET=votre_secret_ici
```

### 🐳 Docker (Optionnel)
```bash
# Démarrer avec Docker
docker-compose up -d

# Accéder à l'application
http://localhost:8080

# Mailhog (emails)
http://localhost:8025
```

---

## 📱 Utilisation

### 👤 Utilisateur Standard
1. **Inscription** : Créer un compte sur la page d'accueil
2. **Parcourir** : Consulter le catalogue de véhicules
3. **Rechercher** : Utiliser les filtres pour trouver le véhicule idéal
4. **Commander** : Procéder au paiement sécurisé via Stripe
5. **Suivre** : Consulter l'historique des commandes

### 🔧 Administrateur
1. **Connexion** : Se connecter avec les droits admin
2. **Gérer** : Ajouter/modifier/supprimer des véhicules
3. **Utilisateurs** : Gérer les comptes utilisateurs
4. **Commandes** : Suivre et gérer les commandes
5. **Statistiques** : Consulter les rapports de vente

### 🛠️ Commandes Console Utiles
```bash
# Gestion des super admins
php bin/console app:make-super-admin email@example.com
php bin/console app:remove-super-admin email@example.com
php bin/console app:show-super-admin

# Test des webhooks Stripe
php bin/console app:stripe-webhook

# Test des emails
php bin/console app:test-email
```

---

## 🔧 Développement

### 🎨 Styles et JavaScript
```bash
# Structure des assets
public/
├── css/           # Styles CSS
│   ├── main.css   # Styles globaux
│   ├── home.css   # Page d'accueil
│   ├── vehicules.css # Liste des véhicules
│   └── ...
├── js/            # JavaScript
│   ├── app.js     # Script principal
│   ├── carousel.js # Carrousel interactif
│   └── navigation.js # Navigation mobile
└── images/        # Images et logos
```

### 🧪 Tests
```bash
# Lancer les tests
php bin/phpunit

# Tests avec couverture
php bin/phpunit --coverage-html var/coverage
```

### 📊 Monitoring
```bash
# Profiler Symfony
http://localhost:8000/_profiler

# Logs d'application
tail -f var/log/dev.log
```

---

## 📊 Structure du Projet

```
symfony_quernel-auto/
├── app/                          # Application Symfony
│   ├── config/                   # Configuration
│   ├── migrations/               # Migrations Doctrine
│   ├── public/                   # Assets publics
│   │   ├── css/                  # Styles CSS
│   │   ├── js/                   # JavaScript
│   │   ├── images/               # Images
│   │   └── uploads/              # Uploads utilisateurs
│   ├── src/                      # Code source
│   │   ├── Command/              # Commandes console
│   │   ├── Controller/           # Contrôleurs
│   │   ├── Entity/               # Entités Doctrine
│   │   ├── Form/                 # Formulaires
│   │   ├── Repository/           # Repositories
│   │   └── Service/              # Services
│   ├── templates/                # Templates Twig
│   └── var/                      # Cache et logs
├── docs/                         # Documentation
├── docker-compose.yaml           # Configuration Docker
└── README.md                     # Ce fichier
```

---

## 🤝 Contribution

### 🐛 Signaler un Bug
1. Vérifier que le bug n'a pas déjà été signalé
2. Créer une issue avec une description détaillée
3. Inclure les étapes pour reproduire le problème
4. Ajouter des captures d'écran si nécessaire

### 💡 Proposer une Amélioration
1. Créer une issue avec le label "enhancement"
2. Décrire l'amélioration souhaitée
3. Expliquer les bénéfices pour les utilisateurs

### 🔧 Contribuer au Code
1. Fork le repository
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements avec des messages clairs
4. Créer une Pull Request avec une description détaillée

### 📝 Standards de Code
- **PHP** : PSR-12
- **JavaScript** : ESLint + Prettier
- **CSS** : BEM methodology
- **Git** : Conventional Commits

---

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 📞 Contact

- **Email** : contact@quernel-auto.com
- **Site Web** : https://quernel-auto.com
- **GitHub** : https://github.com/MorvinQUERNEL/symfony_quernel-auto

---

<div align="center">
  <p>Développé avec ❤️ par l'équipe Quernel Auto</p>
  <p>🚗 Simplifions l'import/export de véhicules ensemble !</p>
</div> 