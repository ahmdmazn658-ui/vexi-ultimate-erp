# Stage 1: Build the frontend
FROM node:20 AS frontend-build
WORKDIR /frontend
COPY apps/frontend/package*.json ./
RUN npm install
COPY apps/frontend/ ./
RUN npm run build

# Stage 2: Build the backend and merge frontend assets
FROM php:8.3-fpm
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql mbstring xml
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY apps/backend/ .
RUN mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs && chmod -R 775 bootstrap/cache storage
RUN composer install --no-dev --optimize-autoloader
COPY --from=frontend-build /frontend/dist/ ./public/
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
ENV PORT=10000
EXPOSE 10000
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=10000
