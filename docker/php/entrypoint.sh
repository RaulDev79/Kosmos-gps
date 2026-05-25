#!/bin/sh
set -e

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
fi

if grep -q "^APP_KEY=$" /var/www/.env; then
    APP_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/.env
fi

exec "$@"
