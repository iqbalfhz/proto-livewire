# syntax=docker/dockerfile:1

# ─── Stage 1: Build frontend assets ──────────────────────────────────────────
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --frozen-lockfile

COPY . .
RUN npm run build

# ─── Stage 2: Install PHP dependencies ───────────────────────────────────────
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize

# ─── Stage 3: Production image (FrankenPHP) ──────────────────────────────────
FROM dunglas/frankenphp:latest-php8.4-alpine

# Install required PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    redis \
    pcntl \
    opcache \
    zip \
    gd \
    exif \
    intl \
    bcmath \
    mbstring

# Set working directory (FrankenPHP serves from here)
WORKDIR /app

# Copy vendor from composer stage
COPY --from=composer-builder /app/vendor /app/vendor

# Copy built frontend assets
COPY --from=node-builder /app/public/build /app/public/build

# Copy application source
COPY . .

# Ensure writable directories exist (bootstrap/cache excluded in .dockerignore)
RUN mkdir -p /app/bootstrap/cache /app/storage/framework/sessions \
        /app/storage/framework/views /app/storage/framework/cache \
        /app/storage/logs /app/storage/app/public \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Copy FrankenPHP/Caddy config
COPY Caddyfile /etc/caddy/Caddyfile

# Copy and set entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose HTTP and HTTPS ports
EXPOSE 80 443 443/udp

# Run entrypoint (migrates, caches, then starts FrankenPHP)
CMD ["/usr/local/bin/docker-entrypoint.sh"]
