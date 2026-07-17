#!/bin/sh
set -eu

if [ "$APP_ENV" != "production" ] || [ "$APP_DEBUG" != "false" ]; then
    echo "Fatal: scheduler requires APP_ENV=production and APP_DEBUG=false."
    exit 1
fi

if [ "$DB_CONNECTION" != "mysql" ] || [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo "Fatal: scheduler requires a complete MySQL production configuration."
    exit 1
fi

exec php artisan schedule:work
