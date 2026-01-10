# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Quernel Auto is a vehicle import/export platform built with Symfony 7.3 and PHP 8.2+. It features a vehicle catalog, user management, Stripe payment integration, and an admin interface.

## Development Commands

All commands should be run from the `app/` directory:

```bash
# Install dependencies
composer install

# Database operations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:update --force

# Clear cache
php bin/console cache:clear

# Run tests
php bin/console test
./vendor/bin/phpunit
./vendor/bin/phpunit tests/Path/To/TestFile.php  # Single test file

# Static analysis (PHPStan level 5)
./vendor/bin/phpstan analyse

# Create/manage super admin users
php bin/console app:make-super-admin user@email.com
php bin/console app:remove-super-admin user@email.com
php bin/console app:show-super-admin

# Test email configuration
php bin/console app:test-email recipient@email.com

# Clean expired orders
php bin/console app:clean-expired-orders
```

## Docker Development

```bash
# Start all services (from project root)
docker-compose up -d

# Access points:
# - App: http://localhost:8080
# - phpMyAdmin: http://localhost:8081
# - Mailhog: http://localhost:8025
```

## Architecture

### Directory Structure
- `app/` - Symfony application root
- `app/src/` - PHP source code (PSR-4: `App\`)
- `app/templates/` - Twig templates
- `app/public/` - Web root and assets
- `app/config/` - Symfony configuration
- `php/` - Docker PHP/Apache configuration
- `mysql/` - MySQL data volume

### Core Entities
- **Users** - User accounts with roles (ROLE_USER, ROLE_ADMIN, ROLE_SUPER_ADMIN)
- **Vehicules** - Vehicle listings with brand, model, price, status
- **Orders** - Purchase orders linking users to vehicles
- **Payement** - Payment records (Stripe integration)
- **Messages** - User-to-user/admin messaging
- **Pictures** - Vehicle images (OneToMany with Vehicules)
- **Preference** - User preferences

### Key Services
- `StripeService` - Payment session creation, payment processing
- `EmailService` - Transactional emails
- `VehiculeStatusService` - Vehicle availability management
- `PictureUploadService` - Image upload handling

### Security Configuration
Role hierarchy: `ROLE_SUPER_ADMIN > ROLE_ADMIN > ROLE_USER`

Public routes: home, login, registration, vehicle catalog, legal pages
Protected routes:
- `/profile`, `/orders`, `/preference` - ROLE_USER
- `/admin` - ROLE_ADMIN
- `/users`, `/roles`, `/admin/settings` - ROLE_SUPER_ADMIN

### Database
MySQL 8.0 via Doctrine ORM. Connection configured via `DATABASE_URL` in `.env.local`.
