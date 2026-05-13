#!/bin/bash

set -e

echo "Running migrations and seeding database ..."
php artisan migrate --force

php artisan storage:link
php artisan optimize:clear
php artisan optimize

echo "Starting Laravel server ..."

# Start the FrankenPHP server
exec frankenphp run --config /Caddyfile --adapter caddyfile 2>&1
