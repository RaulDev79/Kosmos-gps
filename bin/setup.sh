#!/bin/bash
set -e

echo "=== Kosmos GPS - Docker Setup ==="

echo ""
echo "1. Construyendo imágenes Docker..."
docker compose build

echo ""
echo "2. Iniciando contenedores..."
docker compose up -d

echo ""
echo "   Esperando a que el contenedor app esté listo..."
until docker compose exec app php -v > /dev/null 2>&1; do
  sleep 1
done

echo ""
echo "3. Corrigiendo permisos y ejecutando migraciones + seeders..."
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec -u www-data app php artisan migrate --force --seed

echo ""
echo "=== Setup completo ==="
echo "App corriendo en: http://localhost:${APP_PORT:-18080}"
echo ""
echo "Comandos útiles:"
echo "  docker compose logs -f app    # Ver logs de la aplicación"
echo "  docker compose exec app bash  # Abrir shell en el contenedor app"
echo "  docker compose down           # Detener todos los servicios"
