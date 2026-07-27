# Onboarding script for Windows 10/11 (PowerShell)
$ErrorActionPreference = "Stop"

# ─── Detect OS ────────────────────────────────────────────────────────────────
$OS = [System.Environment]::OSVersion.Platform
if ($OS -ne "Win32NT") {
    Write-Error "This script is intended for Windows. Use onboarding.sh on Linux/macOS."
    exit 1
}

$Version = [System.Environment]::OSVersion.Version
Write-Host "Detected platform: Windows $($Version.Major).$($Version.Minor)"

# ─── Copy .env ────────────────────────────────────────────────────────────────
if (-Not (Test-Path ".env")) {
    Write-Host "Copying .env.example to .env..."
    Copy-Item ".env.example" ".env"
} else {
    Write-Host ".env already exists, skipping copy."
}

# ─── Copy dish images ─────────────────────────────────────────────────────────
Write-Host "Copying dish images to storage..."
New-Item -ItemType Directory -Force -Path "storage/app/public/dishes" | Out-Null
if (Test-Path "../docs/images") {
    Copy-Item "../docs/images/*.jpg" -Destination "storage/app/public/dishes/" -ErrorAction SilentlyContinue
    Copy-Item "../docs/images/*.webp" -Destination "storage/app/public/dishes/" -ErrorAction SilentlyContinue
    Write-Host "Images copied successfully."
} else {
    Write-Host "Warning: ../docs/images directory not found. Images may not be available for seeding."
}

# ─── Install dependencies ─────────────────────────────────────────────────────
Write-Host "Running npm install..."
npm install

Write-Host "Running composer install..."
composer install

Write-Host "Creating storage link..."
php artisan storage:link

Write-Host "Migrating database and seeding..."
php artisan key:generate
php artisan migrate:fresh --seed

# ─── Start dev server ─────────────────────────────────────────────────────────
Write-Host "Starting development server..."
composer run dev
