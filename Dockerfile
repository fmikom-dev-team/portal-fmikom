# syntax=docker/dockerfile:1.4
# Stage 1: Install Composer Dependencies & Generate Wayfinder
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
# Cache composer downloads between builds — speeds up subsequent deploys significantly
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative
# Generate wayfinder route files (requires php, available in this stage via composer image)
RUN php artisan wayfinder:generate || true

# Stage 2: Build Frontend Assets
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package.json package-lock.json ./
# Cache npm downloads between builds — skips re-downloading unchanged packages
RUN --mount=type=cache,target=/root/.npm \
    npm ci --legacy-peer-deps
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

# Set container timezone to Asia/Jakarta (WIB, UTC+7)
ENV TZ=Asia/Jakarta

# Install system dependencies (including ffmpeg for Pagi module video handling)
# Cache apk package downloads between builds
RUN --mount=type=cache,target=/var/cache/apk \
    (apk update || (sleep 2 && apk update)) && \
    apk add --no-cache \
    tzdata \
    bash \
    nginx \
    supervisor \
    ffmpeg \
    libpng \
    libzip \
    icu \
    zip \
    unzip \
    git && \
    cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime && \
    echo "Asia/Jakarta" > /etc/timezone

# Copy PHP extension installer to easily install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install required PHP extensions
RUN install-php-extensions pdo_mysql bcmath gd zip opcache pcntl redis exif

# Configure Nginx, PHP, and Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Invalidate cache for application code copy
ARG BUILDTIME=0

# Copy application code
COPY . .

# Copy vendor dependencies from Composer stage
COPY --from=composer-builder /app/vendor ./vendor

# Copy compiled frontend assets from Node stage
COPY --from=frontend-builder /app/public/build ./public/build

# Set permissions for entrypoint and pre-install frankenphp binary if missing
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    if [ -f /var/www/html/frankenphp ]; then \
        chmod +x /var/www/html/frankenphp && cp /var/www/html/frankenphp /usr/local/bin/frankenphp; \
    else \
        curl -fL https://github.com/dunglas/frankenphp/releases/download/v1.5.0/frankenphp-linux-x86_64 -o /usr/local/bin/frankenphp && \
        chmod +x /usr/local/bin/frankenphp && cp /usr/local/bin/frankenphp /var/www/html/frankenphp; \
    fi

# Healthcheck for zero-downtime deployments (45s start period for container grace time)
HEALTHCHECK --interval=5s --timeout=3s --start-period=45s --retries=3 \
  CMD curl -f http://localhost:80/up || exit 1

# Expose HTTP and Reverb WebSocket ports
EXPOSE 80 8080

# Configure entrypoint and default start command
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
