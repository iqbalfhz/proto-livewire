# syntax=docker/dockerfile:1

# ─── Stage 1: Install PHP dependencies ───────────────────────────────────────
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

# ─── Stage 2: Build frontend assets ──────────────────────────────────────────
FROM node:26-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json .npmrc ./
RUN npm ci

# Copy vendor files required by Vite/Tailwind (excluded from .dockerignore)
COPY --from=composer-builder /app/vendor/livewire/flux/dist/flux.css /app/vendor/livewire/flux/dist/flux.css
COPY --from=composer-builder /app/vendor/livewire/flux/stubs /app/vendor/livewire/flux/stubs

COPY . .
RUN npm run build

# ─── Stage 3: Production image (FrankenPHP) ──────────────────────────────────
FROM dunglas/frankenphp:1.14-php8.6-alpine

# Only what the app uses: MySQL, queue timeouts, opcache, share-card images, string handling
RUN install-php-extensions \
    pdo_mysql \
    pcntl \
    opcache \
    gd \
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

# PHP ini overrides
RUN printf "upload_max_filesize=12M\npost_max_size=64M\nmax_execution_time=60\n" \
    > /usr/local/etc/php/conf.d/uploads.ini

# Copy FrankenPHP/Caddy config
COPY Caddyfile /etc/caddy/Caddyfile

# Copy and set entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose HTTP and HTTPS ports
EXPOSE 80 443 443/udp

# Run entrypoint (migrates, caches, then starts FrankenPHP)
CMD ["/usr/local/bin/docker-entrypoint.sh"]
