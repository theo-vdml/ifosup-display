# --- Stage 1 : Build complet (PHP + Assets) ---
# On utilise Debian (Bookworm) comme Railpack
FROM dunglas/frankenphp:php8.4-bookworm AS builder

# Installation de Node.js (nécessaire pour Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP minimales pour le build
RUN install-php-extensions pdo_mysql bcmath gd zip intl pcntl opcache

WORKDIR /app

# 1. Installation des dépendances PHP
COPY composer*.json ./
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --no-scripts --no-autoloader

# 2. Installation des dépendances JS
COPY package*.json ./
RUN npm install

# 3. Copie du code et build
COPY . .
RUN composer dump-autoload
# Ici, php est disponible, donc Wayfinder ne plantera pas
RUN npm run build

# --- Stage 2 : Image Finale ---
FROM dunglas/frankenphp:php8.4-bookworm

# Variables Railpack
ENV SERVER_NAME=:80
ENV IS_LARAVEL=true

# Extensions pour le runtime
RUN install-php-extensions pdo_mysql bcmath gd zip intl pcntl opcache

WORKDIR /app

# On récupère tout ce qui a été buildé proprement au Stage 1
COPY --from=builder /app /app

# Nettoyage et optimisations finales
RUN rm -rf node_modules && \
    composer dump-autoload --optimize && \
    php artisan optimize

# Droits d'accès
RUN chown -R www-data:www-data storage bootstrap/cache

# Fichiers de config (Caddyfile + start script)
COPY Caddyfile /Caddyfile
COPY docker-entrypoint.sh /start-container.sh
RUN chmod +x /start-container.sh

EXPOSE 80

ENTRYPOINT ["/start-container.sh"]
