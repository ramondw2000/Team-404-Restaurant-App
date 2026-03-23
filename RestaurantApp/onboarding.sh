#!/usr/bin/env bash
set -euo pipefail

# ─── Detect OS ────────────────────────────────────────────────────────────────
OS="$(uname -s)"
case "$OS" in
    Linux*)   PLATFORM="Linux" ;;
    Darwin*)  PLATFORM="macOS" ;;
    *)        echo "Unsupported OS: $OS. Use onboarding.ps1 on Windows."; exit 1 ;;
esac

echo "Detected platform: $PLATFORM"

# ─── Copy .env ────────────────────────────────────────────────────────────────
if [ ! -f .env ]; then
    echo "Copying .env.example to .env..."
    cp .env.example .env
else
    echo ".env already exists, skipping copy."
fi

# ─── Install dependencies ─────────────────────────────────────────────────────
echo "Running npm install..."
npm install

echo "Running composer install..."
composer install

echo "Migrating database and seeding..."
php artisan db:wipe
php artisan key:generate
php artisan migrate:fresh --seed
php artisan db:seed

# ─── Start dev server ─────────────────────────────────────────────────────────
echo "Starting development server..."
composer run dev
