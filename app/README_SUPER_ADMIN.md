# Gestion du Super Administrateur

## Contrainte de Super Admin Unique

Le système Quernel Auto implémente une contrainte stricte : **il ne peut y avoir qu'un seul super administrateur** dans le système à la fois.

## Commandes Disponibles

### 1. Créer un Super Admin
```bash
php bin/console make:super_admin <email> <password> <firstname> <lastname>
```

**Exemple :**
```bash
php bin/console make:super_admin admin@quernel-auto.com motdepasse123 Jean Dupont
```

**Options :**
- `--force` ou `-f` : Force la création en supprimant le super admin existant

**Exemple avec force :**
```bash
php bin/console make:super_admin admin@quernel-auto.com motdepasse123 Jean Dupont --force
```

### 2. Afficher le Super Admin Actuel
```bash
php bin/console show:super_admin
```

Cette commande affiche toutes les informations du super administrateur actuel.

### 3. Supprimer le Super Admin
```bash
php bin/console remove:super_admin
```

Cette commande supprime le super administrateur existant après confirmation.

## Méthodes Utilitaires

### Dans l'Entité Users

```php
// Vérifier si un utilisateur est super admin
$user->isSuperAdmin();

// Vérifier si un utilisateur est admin (inclut super admin)
$user->isAdmin();
```

### Dans le Repository UsersRepository

```php
// Trouver le super admin existant
$superAdmin = $usersRepository->findSuperAdmin();

// Vérifier s'il existe un super admin
$hasSuperAdmin = $usersRepository->hasSuperAdmin();
```

## Sécurité

- Les formulaires d'inscription et de modification de profil ne permettent pas de définir des rôles
- Seules les commandes console peuvent créer ou modifier des super administrateurs
- La contrainte de super admin unique est vérifiée à chaque création

## Hiérarchie des Rôles

```
ROLE_SUPER_ADMIN > ROLE_ADMIN > ROLE_USER
```

- `ROLE_SUPER_ADMIN` : Accès complet à toutes les fonctionnalités
- `ROLE_ADMIN` : Accès aux fonctionnalités d'administration
- `ROLE_USER` : Accès utilisateur standard

## Gestion des Erreurs

Si vous essayez de créer un super admin alors qu'il en existe déjà un :

1. **Sans l'option --force** : La commande échoue avec un message d'erreur
2. **Avec l'option --force** : L'ancien super admin est supprimé après confirmation

## Recommandations

1. **Sauvegarde** : Toujours faire une sauvegarde avant de supprimer un super admin
2. **Documentation** : Garder une trace des informations du super admin actuel
3. **Sécurité** : Utiliser des mots de passe forts pour le super admin
4. **Accès** : Limiter l'accès aux commandes de gestion du super admin 