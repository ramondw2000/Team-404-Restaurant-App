#!/usr/bin/env bash
set -euo pipefail

# ─────────────────────────────────────────────────────────────────────────────
# Container start script for RestaurantApp.
# Modelled on onboarding.sh, but split for a container: dependencies and assets
# are already baked into the image at build time, so this only prepares runtime
# state (storage, database, seed) and then starts the server.
# All configuration comes from environment variables (see .env.docker).
# ─────────────────────────────────────────────────────────────────────────────

cd /var/www/html

DB_CONNECTION="${DB_CONNECTION:-sqlite}"
echo "==> Starting RestaurantApp (DB_CONNECTION=${DB_CONNECTION})"

# ─── Ensure writable storage / cache directories ────────────────────────────
echo "==> Preparing storage directories"
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/logs \
    bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache || true

# ─── Decide whether this boot needs a fresh seed ────────────────────────────
needs_seed=false
if [ "${DB_SEED:-false}" = "true" ]; then
    needs_seed=true
fi

if [ "${DB_CONNECTION}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/data/database.sqlite}"
    if [ ! -f "${DB_FILE}" ]; then
        echo "==> Creating SQLite database at ${DB_FILE}"
        mkdir -p "$(dirname "${DB_FILE}")"
        touch "${DB_FILE}"
        needs_seed=true
    else
        echo "==> Reusing existing SQLite database at ${DB_FILE}"
    fi
fi

# ─── Wait for an external database (e.g. MySQL) to accept connections ────────
if [ "${DB_CONNECTION}" != "sqlite" ]; then
    echo "==> Waiting for database to become available"
    for attempt in $(seq 1 30); do
        if php artisan db:show >/dev/null 2>&1; then
            echo "==> Database is reachable"
            break
        fi
        if [ "${attempt}" -eq 30 ]; then
            echo "!!! Database not reachable after 30 attempts; continuing anyway"
        fi
        sleep 2
    done
fi

# ─── Link public storage so seeded dish images are web-accessible ────────────
echo "==> Linking public storage"
php artisan storage:link --force || true

# ─── Clear any stale cached config/routes/views ──────────────────────────────
php artisan optimize:clear || true

# ─── Migrate (and seed on first boot, mirroring onboarding.sh) ───────────────
if [ "${needs_seed}" = "true" ]; then
    echo "==> Running fresh migration + seeding"
    php artisan migrate:fresh --seed --force
else
    echo "==> Applying pending migrations"
    php artisan migrate --force
fi

# ─── Start the application ───────────────────────────────────────────────────
echo "==> Serving on http://0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
