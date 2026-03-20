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

# ─── Database Setup (Manual Steps Required) ────────────────────────────────────
# NOTE: The following steps must be run manually or added to this script:
#
# 1. Generate application key:
#    php artisan key:generate
#
# 2. Run database migrations to create tables:
#    php artisan migrate --force
#
# 3. Seed the database with roles and test user:
#    php artisan db:seed
#
# These steps set up the database schema, create initial roles (management, server, 
# chef, receptionist), and create a test user with email 'test@example.com' and 
# role 'management'. Without these steps, you will encounter "credentials do not 
# exist" errors when trying to log in.

# ─── Start dev server ─────────────────────────────────────────────────────────
echo "Starting development server..."
composer run dev
