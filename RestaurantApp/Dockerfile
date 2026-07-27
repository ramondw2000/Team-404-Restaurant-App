# syntax=docker/dockerfile:1

# ─────────────────────────────────────────────────────────────────────────────
# Stage 1 — Build front-end assets (Vite + Tailwind) with the official Node image
# ─────────────────────────────────────────────────────────────────────────────
FROM node:22-bookworm-slim AS assets

WORKDIR /app

# Install JS deps from the lockfile first so this layer caches independently.
COPY package.json package-lock.json ./
RUN npm ci

# Tailwind purges against blade views / app PHP, so the full source is needed
# for the production build to emit the correct CSS.
COPY . .
RUN npm run build


# ─────────────────────────────────────────────────────────────────────────────
# Stage 2 — Application runtime: PHP 8.5 (CLI) from the official Sury repository
# ─────────────────────────────────────────────────────────────────────────────
FROM debian:bookworm-slim AS app

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Add packages.sury.org — the canonical Debian repository for current PHP
# releases — then install PHP 8.5 with the extensions this Laravel app needs.
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg apt-transport-https lsb-release unzip git \
 && curl -fsSLo /usr/share/keyrings/sury-php.gpg https://packages.sury.org/php/apt.gpg \
 && echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ bookworm main" \
        > /etc/apt/sources.list.d/sury-php.list \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
        php8.5-cli \
        php8.5-common \
        php8.5-mbstring \
        php8.5-xml \
        php8.5-bcmath \
        php8.5-curl \
        php8.5-zip \
        php8.5-sqlite3 \
        php8.5-mysql \
        php8.5-intl \
        php8.5-gd \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Composer from its official image — no curl-pipe install needed.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first (cached on the lockfile). Skip scripts here because the
# full app isn't copied yet; package discovery runs after the source is in place.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --prefer-dist \
        --no-progress \
        --optimize-autoloader

# Application source, then the compiled assets from the Node stage.
COPY . .
COPY --from=assets /app/public/build ./public/build

# Ensure the framework's writable directories exist (the build context may omit
# the empty cache/session/view folders), then regenerate the optimized
# autoloader and run package discovery.
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
 && composer dump-autoload --no-dev --optimize \
 && chmod +x docker/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
