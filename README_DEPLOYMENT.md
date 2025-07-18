# 🚀 Déploiement Quernel Auto

Ce guide explique comment déployer l'application Symfony **Quernel Auto** (site e-commerce de vente de véhicules) en utilisant GitHub Actions.

## 📋 Vue d'ensemble

Le déploiement utilise GitHub Actions pour automatiser le processus de build et de déploiement. Quand vous poussez du code sur la branche `main`, GitHub Actions génère automatiquement un build et le pousse sur la branche `build`.

## 🔄 Workflow de déploiement

### Déclenchement automatique
- **Branche source** : `main`
- **Branche de déploiement** : `build`
- **Dossier déployé** : `app/`

### Étapes du déploiement
1. **Push sur main** → Déclenche le workflow
2. **Checkout** → Récupération du code
3. **Build** → Génération des assets
4. **Push** → Envoi vers la branche `build`

## ⚙️ Configuration GitHub Actions

Le workflow est configuré dans `.github/workflows/publish.yml` :

```yaml
name: Generate a build and push to another branch

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest
    name: Push
    steps:
      - uses: actions/checkout@main

      - name: Deploy
        uses: s0/git-publish-subdir-action@develop
        env:
          REPO: self
          BRANCH: build
          FOLDER: app
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          MESSAGE: "Build: ({sha}) {msg}"
```

## 🛠️ Déploiement manuel

### 1. Préparer le code
```bash
# Assurez-vous d'être sur la branche main
git checkout main

# Mettre à jour le code
git pull origin main

# Vérifier que tout fonctionne localement
cd app
composer install
php bin/console cache:clear
```

### 2. Déployer
```bash
# Pousser les modifications
git add .
git commit -m "feat: nouvelle fonctionnalité"
git push origin main
```

Le déploiement se déclenche automatiquement après le push.

## 🐳 Déploiement local avec Docker

### Prérequis
- Docker et Docker Compose installés
- Git configuré

### Étapes
```bash
# 1. Cloner le repository
git clone <votre-repo>
cd symfony_quernel-auto

# 2. Démarrer les services
docker-compose up -d

# 3. Installer les dépendances
docker-compose exec php composer install

# 4. Configurer la base de données
docker-compose exec php php bin/console doctrine:migrations:migrate

# 5. Vider le cache
docker-compose exec php php bin/console cache:clear
```

### Accès aux services
- **Application** : http://localhost:8080
- **PHPMyAdmin** : http://localhost:8081
- **MailHog** : http://localhost:8025

## 🔧 Configuration serveur de production

### Variables d'environnement
Créez un fichier `.env.local` dans le dossier `app/` :

```bash
# Application
APP_ENV=prod
APP_SECRET=votre-secret-ici
APP_DEBUG=false

# Base de données
DATABASE_URL="mysql://user:password@localhost:3306/quernel_auto?serverVersion=8.0&charset=utf8mb4"

# Stripe
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Email
MAILER_DSN=smtp://user:password@smtp.gmail.com:587
```

### Configuration Apache
Utilisez le fichier `apache/default.conf` fourni ou créez votre propre configuration :

```apache
<VirtualHost *:80>
    ServerName quernel-auto.com
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/quernel-auto_error.log
    CustomLog ${APACHE_LOG_DIR}/quernel-auto_access.log combined
</VirtualHost>
```

## 📊 Monitoring du déploiement

### Vérifier le statut
1. Allez sur votre repository GitHub
2. Cliquez sur l'onglet "Actions"
3. Vérifiez le statut du workflow "Generate a build and push to another branch"

### Logs utiles
```bash
# Logs Docker
docker-compose logs php
docker-compose logs database

# Logs Symfony
docker-compose exec php tail -f var/log/dev.log
```

## 🔒 Sécurité

### Fichiers sensibles
- Ne jamais commiter `.env.local`
- Protéger les clés API Stripe
- Sécuriser les accès à la base de données

### Permissions
```bash
# Sur le serveur de production
chmod -R 755 var/
chown -R www-data:www-data var/
```

## 🛠️ Dépannage

### Problèmes courants

#### 1. Workflow GitHub Actions échoue
- Vérifiez les logs dans l'onglet Actions
- Assurez-vous que la branche `main` existe
- Vérifiez les permissions du repository

#### 2. Erreur de base de données
```bash
# Vérifier la connexion
docker-compose exec php php bin/console doctrine:query:sql "SELECT 1"

# Vérifier les migrations
docker-compose exec php php bin/console doctrine:migrations:status
```

#### 3. Erreur de cache
```bash
# Vider le cache
docker-compose exec php php bin/console cache:clear
docker-compose exec php php bin/console cache:warmup
```

#### 4. Problème de permissions
```bash
# Corriger les permissions
docker-compose exec php chmod -R 755 var/
docker-compose exec php chown -R www-data:www-data var/
```

## 📞 Support

Pour toute question ou problème :
- **Issues GitHub** : [Créer une issue](https://github.com/votre-repo/issues)
- **Documentation Symfony** : [symfony.com](https://symfony.com/doc/current/deployment.html)

---

**Version** : 1.0.0  
**Dernière mise à jour** : $(date)  
**Auteur** : Équipe Quernel Auto 