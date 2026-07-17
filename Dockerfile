# Etapa 1: compilación de recursos con Node.js
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Etapa 2: aplicación PHP optimizada para Laravel Octane
FROM php:8.4-cli

# Instala las dependencias del sistema y las extensiones de PHP.
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    libwebp-dev \
    libbrotli-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip intl sockets opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache

# Instala Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Define el directorio de trabajo.
WORKDIR /var/www/html

# Copia los archivos de la aplicación.
COPY . .

# Copia los recursos generados en la primera etapa.
COPY --from=assets /app/public/build ./public/build

# Copia la configuración optimizada de PHP.
COPY php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Instala las dependencias PHP de producción.
RUN composer install --no-dev --classmap-authoritative --no-interaction --no-progress

# Incluye RoadRunner en la imagen para no descargar binarios durante el arranque.
RUN vendor/bin/rr get-binary

# Establece los permisos requeridos por Laravel.
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expone el puerto utilizado por Octane.
EXPOSE 8000

# Configura el script de entrada del contenedor.
COPY docker-entrypoint.sh /usr/local/bin/
COPY docker-scheduler-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh /usr/local/bin/docker-scheduler-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sh", "-c", "php artisan octane:start --server=roadrunner --host=0.0.0.0 --port=8000 --workers=${OCTANE_WORKERS:-8} --max-requests=${OCTANE_MAX_REQUESTS:-1000}"]
