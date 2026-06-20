# Stage 1: Install Composer Dependencies & Generate Wayfinder
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative
# Generate wayfinder route files (requires php, available in this stage via composer image)
RUN php artisan wayfinder:generate || true

# Stage 2: Build Frontend Assets
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# Copy wayfinder-generated files from composer stage so vite plugin skips re-generating
COPY --from=composer-builder /app/resources/js/wayfinder ./resources/js/wayfinder
COPY --from=composer-builder /app/resources/js/routes ./resources/js/routes
COPY --from=composer-builder /app/resources/js/actions ./resources/js/actions
# SKIP_WAYFINDER=1 tells vite.config.ts to exclude the wayfinder plugin (no PHP in this stage)
ENV SKIP_WAYFINDER=1
RUN npm run build

# Stage 3: Production Runtime
FROM php:8.4-fpm-alpine AS production-runtime

# Install system dependencies (including ffmpeg for Pagi module video handling)
RUN apk add --no-cache \
    nginx \
    supervisor \
    ffmpeg \
    libpng \
    libzip \
    icu \
    zip \
    unzip \
    git

# Copy PHP extension installer to easily install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install required PHP extensions
RUN install-php-extensions pdo_mysql bcmath gd zip opcache pcntl redis exif

# Configure Nginx, PHP, and Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor dependencies from Composer stage
COPY --from=composer-builder /app/vendor ./vendor

# Copy compiled frontend assets from Node stage
COPY --from=frontend-builder /app/public/build ./public/build

# Set permissions for entrypoint
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose HTTP port
EXPOSE 80

# Configure entrypoint and default start command
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
