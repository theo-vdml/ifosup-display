# --- Stage 1 : Build Assets (Node) ---
FROM node:22-bookworm AS assets-builder
WORKDIR /app
COPY . .
RUN npm install && npm run build

# --- Stage 2 : Final Image (FrankenPHP sur Debian) ---
FROM dunglas/frankenphp:php8.4-bookworm

# Variables Railpack
ENV SERVER_NAME=:80

# Installation des extensions via le helper FrankenPHP
RUN install-php-extensions pdo_mysql bcmath gd zip intl pcntl opcache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copie de tout le projet (y compris le build du stage 1)
COPY . .
COPY --from=assets-builder /app/public/build ./public/build

# Installation Composer & Optimisation
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN php artisan config:cache && php artisan route:cache

# Droits d'accès
RUN chown -R www-data:www-data storage bootstrap/cache

# Fichiers Railpack (on les simule)
COPY Caddyfile /Caddyfile
COPY start-container.sh /start-container.sh
RUN chmod +x /start-container.sh

EXPOSE 80

ENTRYPOINT ["/start-container.sh"]
