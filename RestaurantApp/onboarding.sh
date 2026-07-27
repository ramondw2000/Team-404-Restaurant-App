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

# ─── Copy dish images ─────────────────────────────────────────────────────────
echo "Copying dish images to storage..."
mkdir -p storage/app/public/dishes
if [ -d "../docs/images" ]; then
    cp "../docs/images"/*.jpg storage/app/public/dishes/
    cp "../docs/images"/*.webp storage/app/public/dishes/ 2>/dev/null || true
    echo "Images copied successfully."
else
    echo "Warning: ../docs/images directory not found. Images may not be available for seeding."
fi

# ─── Install dependencies ─────────────────────────────────────────────────────
echo "Running npm install..."
npm install

echo "Running composer install..."
composer install

echo "Creating storage link..."
php artisan storage:link

echo "Migrating database and seeding..."
php artisan key:generate
php artisan migrate:fresh --seed

# ─── Start dev server ─────────────────────────────────────────────────────────
echo "Starting development server..."
composer run dev
