# --- Stage 1 : Build assets ---
FROM php:8.4-alpine AS assets-builder

RUN apk add --no-cache nodejs npm
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1. Installation des dépendances PHP (ignore les extensions manquantes au build)
COPY composer*.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

# 2. Installation des dépendances JS
COPY package*.json ./
RUN npm install

# 3. Copie du code et build JS
COPY . .
RUN composer dump-autoload --ignore-platform-reqs
RUN npm run build

# --- Stage 2 : Image Finale FrankenPHP ---
FROM dunglas/frankenphp:1.3-php8.4-alpine

# On installe Composer ICI AUSSI pour le dump-autoload final
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Extensions PHP pour le runtime
RUN apk add --no-cache \
    zip libpq-dev libpng-dev libzip-dev icu-dev oniguruma-dev \
    && install-php-extensions pdo_mysql bcmath gd zip intl pcntl

WORKDIR /app

# On récupère le code du builder
COPY --from=assets-builder /app /app

# Nettoyage node_modules et optimisation (les extensions sont là maintenant)
RUN rm -rf node_modules \
    && composer dump-autoload --optimize \
    && php artisan optimize

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

COPY Caddyfile /etc/caddy/Caddyfile

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
