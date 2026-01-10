#!/bin/bash
# Script d'initialisation des données de démonstration
# À exécuter après le déploiement ou au premier lancement

set -e

echo "=== Initialisation des données de démonstration Quernel Auto ==="

# Attendre que la base de données soit prête
echo "Attente de la base de données..."
sleep 5

# Exécuter les migrations
echo "Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Créer les véhicules de démonstration
echo "Création des véhicules de démonstration..."
php bin/console app:seed-demo-vehicules --force

echo "=== Initialisation terminée ==="
