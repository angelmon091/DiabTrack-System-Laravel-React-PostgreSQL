#!/bin/bash
set -e

# Impide iniciar producción con valores de prueba o configuraciones inseguras.
if [ "$REQUIRE_PRODUCTION_ENV" = "true" ]; then
    if [ "$APP_ENV" != "production" ]; then
        echo "Fatal: production container requires APP_ENV=production."
        exit 1
    fi

    if [ "$APP_DEBUG" != "false" ]; then
        echo "Fatal: production container requires APP_DEBUG=false."
        exit 1
    fi

    if [ "$DB_CONNECTION" != "mysql" ] || [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
        echo "Fatal: invalid production database configuration."
        exit 1
    fi
fi

# Limpia las cachés para cargar las variables actuales del entorno.
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Crea el enlace público hacia el almacenamiento.
php artisan storage:link --force

# Espera hasta que la base de datos acepte conexiones.
if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Waiting for $DB_CONNECTION to be ready..."
    max_tries=30
    count=0
    
    # Comprueba la conexión con PDO sin depender del cargador de Laravel.
    until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
        sleep 2
        count=$((count + 1))
        if [ $count -gt $max_tries ]; then
            echo "Error: Database not ready after 60 seconds."
            # Permite que Laravel intente iniciar y registre el error definitivo.
            break
        fi
    done
    echo "Database check finished."
fi

# Ejecuta las migraciones pendientes.
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Ejecuta los sembradores únicamente cuando se solicite de forma manual.
# echo "Ejecutando sembradores..."
# php artisan db:seed --force

# Genera las cachés de optimización únicamente en producción.
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "Clearing cache for development..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Comprueba que el binario de RoadRunner esté disponible.
if [ ! -f "rr" ]; then
    echo "Installing RoadRunner binary..."
    php artisan octane:install --server=roadrunner --no-interaction
fi

# Ejecuta el comando definido por la imagen.
exec "$@"
