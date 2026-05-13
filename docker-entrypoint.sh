#!/bin/sh
set -e

# Attendre que la DB soit prête (optionnel mais recommandé en prod)
# sleep 5

# Créer le lien symbolique pour le storage
php artisan storage:link --force

# Exécuter les migrations
php artisan migrate --force


# Vos commandes personnalisées
php artisan app:create-admin-user

# Cacher la configuration et les routes pour de meilleures performances
php artisan config:cache
php artisan route:cache

# Lancer la commande principale (php-fpm)
exec "$@"
