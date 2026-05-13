#!/bin/sh
set -e

# Migration de la base de données
echo "Running migrations..."
php artisan migrate --force

# Lien symbolique storage
echo "Linking storage..."
php artisan storage:link --force

# Création de l'admin (ta commande spécifique)
echo "Creating admin user..."
php artisan app:create-admin-user

# Optimisation Laravel
php artisan optimize

# Démarrage de FrankenPHP (la commande par défaut de l'image)
echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile
