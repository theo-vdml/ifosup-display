# --- Stage 1 : Build complet ---
FROM dunglas/frankenphp:php8.4-bookworm AS builder

# Node.js pour Vite
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions pdo_mysql bcmath gd zip intl pcntl opcache

WORKDIR /app
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer*.json ./
RUN composer install --no-dev --no-scripts --no-autoloader

COPY package*.json ./
RUN npm install

COPY . .
RUN composer dump-autoload
RUN npm run build

# --- Stage 2 : Image Finale ---
FROM dunglas/frankenphp:php8.4-bookworm

# RE-INSTALLER COMPOSER ICI AUSSI
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN install-php-extensions pdo_mysql bcmath gd zip intl pcntl opcache

WORKDIR /app

# On récupère le build
COPY --from=builder /app /app

# Maintenant composer est présent, cette ligne va fonctionner
RUN rm -rf node_modules && \
    composer dump-autoload --optimize && \
    php artisan optimize

RUN chown -R www-data:www-data storage bootstrap/cache

# Configuration Railpack
COPY Caddyfile /Caddyfile
COPY docker-entrypoint.sh /start-container.sh
RUN chmod +x /start-container.sh

# Variables d'environnement pour le Caddyfile
ENV PORT=80
ENV SERVER_NAME=:80
EXPOSE 80

ENTRYPOINT ["/start-container.sh"]
