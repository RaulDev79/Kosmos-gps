#!/bin/bash
set -e

echo "=== Kosmos GPS - Docker Setup ==="

echo ""
echo "1. Building Docker images..."
docker compose build

echo ""
echo "2. Starting containers..."
docker compose up -d

echo ""
echo "3. Generating application key..."
docker compose exec app php artisan key:generate --ansi

echo ""
echo "4. Running database migrations..."
docker compose exec app php artisan migrate --force

echo ""
echo "=== Setup complete ==="
echo "App running at: http://localhost:${APP_PORT:-8080}"
echo ""
echo "Useful commands:"
echo "  docker compose logs -f app    # Follow app logs"
echo "  docker compose exec app bash  # Open shell in app container"
echo "  docker compose down           # Stop all services"
