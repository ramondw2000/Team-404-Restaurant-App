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

# ─── Install dependencies ─────────────────────────────────────────────────────
Write-Host "Running npm install..."
npm install

Write-Host "Running composer install..."
composer install

# ─── Start dev server ─────────────────────────────────────────────────────────
Write-Host "Starting development server..."
composer run dev
