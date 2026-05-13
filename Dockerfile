# --- Étape 1 : Build des assets JS ---
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Étape 2 : Runtime PHP ---
FROM php:8.2-fpm-alpine

# Installation des extensions système et PHP nécessaires
RUN apk add --no-cache \
    zip \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo_mysql bcmath gd zip intl

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copie du code source
COPY . .
# On récupère les assets compilés du premier stage
COPY --from=assets-builder /app/public/build ./public/build

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Permissions pour Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Script d'entrée pour gérer les migrations et commandes spécifiques
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
