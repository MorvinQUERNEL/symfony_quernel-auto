# 🚗 Quernel Auto - Plateforme d'Import/Export de Véhicules

<div align="center">
  <img src="public/images/logo_quernel_auto.png" alt="Quernel Auto Logo" width="300" height="auto">
  
  [![Symfony](https://img.shields.io/badge/Symfony-6.4+-000000?style=for-the-badge&logo=symfony)](https://symfony.com/)
  [![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
  [![Docker](https://img.shields.io/badge/Docker-20.10+-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
  [![Stripe](https://img.shields.io/badge/Stripe-008CDD?style=for-the-badge&logo=stripe&logoColor=white)](https://stripe.com/)
  
  **Plateforme moderne d'import/export de véhicules avec paiements sécurisés et support en temps réel**
</div>

---

## 📋 Table des Matières

- [🎯 À Propos](#-à-propos)
- [✨ Fonctionnalités](#-fonctionnalités)
- [🆕 Nouvelles Fonctionnalités](#-nouvelles-fonctionnalités)
- [🏗️ Architecture](#️-architecture)
- [🚀 Installation](#-installation)
- [⚙️ Configuration](#️-configuration)
- [📱 Utilisation](#-utilisation)
- [🔧 Développement](#-développement)
- [📊 Structure du Projet](#-structure-du-projet)
- [🐛 Corrections et Améliorations](#-corrections-et-améliorations)
- [🤝 Contribution](#-contribution)
- [📄 Licence](#-licence)

---

## 🎯 À Propos

**Quernel Auto** est une plateforme web moderne développée avec Symfony 6, spécialisée dans l'import et l'export de véhicules à l'international. Notre solution offre une expérience utilisateur fluide et sécurisée pour l'achat et la vente de véhicules de qualité, avec un support client en temps réel.

### 🎨 Design Philosophie
- **Design moderne et sobre** avec une palette de couleurs professionnelle
- **Interface responsive** optimisée pour tous les appareils (Mobile First)
- **Expérience utilisateur intuitive** avec navigation fluide
- **Accessibilité** conforme aux standards WCAG
- **Performance optimisée** avec chargement rapide des pages

---

## ✨ Fonctionnalités

### 🚗 Gestion des Véhicules
- **Catalogue complet** avec photos haute qualité
- **Recherche avancée** par marque, modèle, carburant, couleur
- **Filtres dynamiques** pour affiner les résultats
- **Carrousel interactif** sur la page d'accueil
- **Statuts en temps réel** (Disponible, Vendu, En cours, etc.)
- **Gestion des images** avec upload sécurisé
- **Prix dynamiques** avec gestion des devises

### 👤 Gestion des Utilisateurs
- **Inscription/Connexion** sécurisée avec validation
- **Profils personnalisés** avec préférences de véhicules
- **Gestion des commandes** et historique complet
- **Système de rôles** (Utilisateur, Admin, Super Admin)
- **Réinitialisation de mot de passe** sécurisée
- **Gestion des erreurs** avec messages clairs

### 💳 Paiements Sécurisés
- **Intégration Stripe** pour les paiements
- **Webhooks sécurisés** pour les notifications
- **Gestion des commandes** automatisée
- **Emails de confirmation** automatiques
- **Support Apple Pay** (préparé)
- **Gestion des erreurs de paiement**

### 📧 Communication et Support
- **Système de chat en temps réel** entre utilisateurs et admins
- **Formulaire de contact** avec validation
- **Notifications par email** (Symfony Mailer)
- **Messages flash** pour le feedback utilisateur
- **Support multilingue** (préparé)
- **Emails transactionnels** automatiques

### 🛠️ Administration
- **Interface d'administration** complète et responsive
- **Gestion des véhicules** (CRUD complet)
- **Gestion des utilisateurs** et permissions
- **Statistiques** et rapports de vente
- **Commandes console** pour la maintenance
- **Gestion des conversations** de support

---

## 🆕 Nouvelles Fonctionnalités

### 💬 Système de Chat en Temps Réel
- **Chat utilisateur** : Interface moderne pour contacter le support
- **Chat admin** : Gestion centralisée de toutes les conversations
- **Messages en temps réel** avec AJAX
- **Indicateurs de messages non lus**
- **Historique des conversations** par utilisateur
- **Sécurité** : Accès réservé aux utilisateurs connectés
- **Interface responsive** adaptée mobile et desktop

### 🔐 Réinitialisation de Mot de Passe
- **Demande de réinitialisation** par email
- **Liens sécurisés** avec expiration automatique (1 heure)
- **Emails personnalisés** avec design professionnel
- **Gestion des erreurs** (email inexistant, lien expiré)
- **Interface utilisateur** moderne et intuitive

### 🎨 Améliorations Interface
- **Navigation mobile-first** avec menu déroulant
- **Menu Services** dynamique selon l'état de connexion
- **Corrections de couleurs** pour une meilleure lisibilité
- **Formulaires optimisés** avec validation en temps réel
- **Messages d'erreur** clairs et informatifs

### 🛡️ Sécurité Renforcée
- **Protection des routes** avec authentification
- **Gestion des exceptions** pour éviter les erreurs 500
- **Validation des données** côté serveur et client
- **CSRF protection** sur tous les formulaires
- **Sessions sécurisées** avec expiration

---

## 🏗️ Architecture

### 🎯 Stack Technique
```
Frontend:
├── Twig (Templates)
├── CSS3 avec variables personnalisées (Mobile First)
├── JavaScript ES6+ (Vanilla)
├── Font Awesome (Icônes)
├── AJAX pour les interactions temps réel
└── Responsive Design (Mobile First)

Backend:
├── Symfony 6.4+
├── PHP 8.1+
├── Doctrine ORM
├── Symfony Security
├── Symfony Mailer
├── Symfony Forms
└── Symfony Messenger (pour les emails)

Base de Données:
├── MySQL 8.0+
├── Migrations Doctrine
├── Entité Messages (pour le chat)
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
│   ├── ChatController.php      # Gestion du chat
│   ├── ResetPasswordController.php # Réinitialisation mot de passe
│   └── ...
├── Entity/         # Entités Doctrine
│   ├── Messages.php            # Messages du chat
│   └── ...
├── Repository/     # Requêtes personnalisées
├── Form/          # Formulaires Symfony
│   ├── ChatMessageType.php     # Formulaire chat
│   └── ...
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
6. **Support** : Utiliser le chat pour contacter l'équipe

### 🔧 Administrateur
1. **Connexion** : Se connecter avec les droits admin
2. **Gérer** : Ajouter/modifier/supprimer des véhicules
3. **Utilisateurs** : Gérer les comptes utilisateurs
4. **Commandes** : Suivre et gérer les commandes
5. **Statistiques** : Consulter les rapports de vente
6. **Support** : Répondre aux messages du chat utilisateur

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
│   ├── chat.css   # Interface du chat
│   ├── reset_password.css # Réinitialisation mot de passe
│   └── ...
├── js/            # JavaScript
│   ├── app.js     # Script principal
│   ├── carousel.js # Carrousel interactif
│   ├── navigation.js # Navigation mobile
│   └── chat.js    # Fonctionnalités chat
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
│   │   │   ├── ChatController.php # Gestion du chat
│   │   │   └── ResetPasswordController.php # Réinitialisation
│   │   ├── Entity/               # Entités Doctrine
│   │   │   └── Messages.php      # Messages du chat
│   │   ├── Form/                 # Formulaires
│   │   │   └── ChatMessageType.php # Formulaire chat
│   │   ├── Repository/           # Repositories
│   │   └── Service/              # Services
│   ├── templates/                # Templates Twig
│   │   ├── chat/                 # Templates du chat
│   │   ├── reset_password/       # Templates réinitialisation
│   │   └── emails/               # Templates d'emails
│   └── var/                      # Cache et logs
├── docs/                         # Documentation
├── docker-compose.yaml           # Configuration Docker
└── README.md                     # Ce fichier
```

---

## 🐛 Corrections et Améliorations

### 🔧 Corrections d'Erreurs
- **Erreur 500** lors de l'inscription avec email dupliqué → Gestion des exceptions
- **Erreur 500** lors de la réinitialisation de mot de passe → Correction du template
- **Problèmes de couleurs** sur les formulaires → Styles CSS corrigés
- **Routes manquantes** dans la navigation → Correction des noms de routes

### 🎨 Améliorations Interface
- **Navigation mobile-first** avec menu déroulant responsive
- **Menu Services** adaptatif selon l'état de connexion
- **Couleurs optimisées** pour une meilleure lisibilité
- **Formulaires modernisés** avec validation améliorée
- **Messages d'erreur** plus clairs et informatifs

### 🛡️ Sécurité
- **Protection du chat** : Accès réservé aux utilisateurs connectés
- **Gestion des exceptions** : Éviter les erreurs 500
- **Validation renforcée** : Côté serveur et client
- **Sessions sécurisées** : Configuration optimisée

### 📱 Responsive Design
- **Approche mobile-first** pour tous les composants
- **Navigation adaptative** avec menu burger
- **Formulaires optimisés** pour mobile
- **Chat responsive** sur tous les appareils

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
- **JavaScript** : ES6+ avec commentaires
- **CSS** : BEM methodology + Mobile First
- **Git** : Conventional Commits
- **Symfony** : Best practices officielles

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
  <p>💬 Support en temps réel disponible pour nos clients</p>
</div> 