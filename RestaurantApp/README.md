# RestaurantApp

A Laravel 12 + Livewire 4 restaurant management application (menus, dishes, orders,
floor plan, reservations and maintenance tasks).

This guide explains how to run the application. The **recommended** way is Docker —
it installs the correct PHP version and all extensions for you and needs no local
PHP, Composer or Node setup. A local (non-Docker) path is documented further down.

---

## Table of contents

- [Run with Docker (recommended)](#run-with-docker-recommended)
  - [1. Install Docker](#1-install-docker)
  - [2. Start the application](#2-start-the-application)
  - [3. Log in](#3-log-in)
  - [Everyday commands](#everyday-commands)
- [Configuration](#configuration)
  - [Changing the port](#changing-the-port)
  - [Switching from SQLite to MySQL](#switching-from-sqlite-to-mysql)
- [What happens on first boot](#what-happens-on-first-boot)
- [Run locally without Docker](#run-locally-without-docker)
- [Running tests](#running-tests)
- [Troubleshooting](#troubleshooting)

---

## Run with Docker (recommended)

### Requirements

- **Docker Engine 20.10+** with the **Compose v2** plugin (`docker compose`, not the
  old `docker-compose`).
- No local PHP, Composer or Node needed — they all live inside the image.

### 1. Install Docker

#### Linux (Debian / Ubuntu)

Install Docker Engine + the Compose plugin from Docker's official APT repository:

```bash
# Remove any distro-shipped versions first
sudo apt-get remove docker docker-engine docker.io containerd runc 2>/dev/null || true

# Add Docker's official GPG key and repository
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
  sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io \
  docker-buildx-plugin docker-compose-plugin

# (Optional) run docker without sudo — log out/in afterwards
sudo usermod -aG docker "$USER"
```

> On Fedora/Arch/openSUSE use your package manager's `docker` and
> `docker-compose-plugin` (or `docker-buildx`) packages, then
> `sudo systemctl enable --now docker`.

#### macOS

Install **Docker Desktop**:

```bash
brew install --cask docker
```

Or download the installer from <https://www.docker.com/products/docker-desktop/>,
then launch Docker Desktop once so the engine starts.

#### Windows

Install **Docker Desktop** (uses the WSL 2 backend):

```powershell
winget install Docker.DockerDesktop
```

Or download it from <https://www.docker.com/products/docker-desktop/>. Make sure
WSL 2 is enabled (`wsl --install`), then start Docker Desktop.

Verify the installation:

```bash
docker --version
docker compose version
```

### 2. Start the application

From the `RestaurantApp/` directory:

```bash
docker compose up --build
```

The first run builds the image (PHP 8.5 from the official Sury repository, Composer
dependencies, and the Vite/Tailwind front-end assets), then creates and seeds a
SQLite database. When you see `Server running on [http://0.0.0.0:8000]`, open:

**<http://localhost:8000>**

Run it in the background with `docker compose up --build -d`.

### 3. Log in

The seeder creates one demo account per role. **All accounts use the password
`password`.**

| Role          | Email                     |
|---------------|---------------------------|
| Manager       | `manager@demo.com`        |
| Server        | `server@demo.com`         |
| Chef          | `chef@demo.com`           |
| Bartender     | `bartender@demo.com`      |
| Receptionist  | `receptionist@demo.com`   |
| Barista       | `barista@demo.com`        |
| Maintenance   | `maintenance@demo.com`    |

### Everyday commands

Run these from the `RestaurantApp/` directory.

| Action | Command |
|--------|---------|
| Start (foreground) | `docker compose up` |
| Start (background) | `docker compose up -d` |
| Rebuild after code changes | `docker compose up --build` |
| Follow logs | `docker compose logs -f` |
| Open a shell in the container | `docker compose exec app bash` |
| Run an artisan command | `docker compose exec app php artisan <command>` |
| Stop the container | `docker compose down` |
| Stop **and wipe** the database + images | `docker compose down -v` |

---

## Configuration

All settings live in **`.env.docker`** — a plain env file kept *outside*
`docker-compose.yml` and loaded into the container via `env_file`. Edit it and
restart (`docker compose up -d`) for changes to take effect. The defaults run the
app on SQLite with no extra services.

Key variables:

| Variable | Default | Purpose |
|----------|---------|---------|
| `APP_ENV` | `local` | Application environment |
| `APP_DEBUG` | `true` | Show detailed errors |
| `APP_PORT` | `8000` | Host port (see below) |
| `DB_CONNECTION` | `sqlite` | Database driver |
| `DB_DATABASE` | `/var/www/html/database/data/database.sqlite` | SQLite file path (absolute) |
| `DB_SEED` | `false` | Force a fresh migrate + seed on next boot |

### Changing the port

Set `APP_PORT` in your shell before starting (the container always serves on 8000
internally; this only changes the published host port):

```bash
APP_PORT=9000 docker compose up -d   # app now on http://localhost:9000
```

### Switching from SQLite to MySQL

1. In `.env.docker` set:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=restaurantapp
   DB_USERNAME=restaurantapp
   DB_PASSWORD=secret
   DB_SEED=true
   ```
2. Add a `mysql` service to `docker-compose.yml` (a commented template is included
   in `.env.docker`).
3. `docker compose up --build`.

The `php8.5-mysql` extension is already installed in the image. The entrypoint
waits for the database to accept connections before migrating.

---

## What happens on first boot

The container's start script (`docker/entrypoint.sh`) mirrors the project's
`onboarding.sh`, split for a container (dependencies and assets are already baked
into the image at build time):

1. Ensures the `storage/` and `bootstrap/cache` directories are writable.
2. Creates the SQLite database file if it does not exist.
3. Links public storage (`php artisan storage:link`) so seeded dish images are
   web-accessible.
4. Clears any stale cached config/routes/views.
5. Runs `migrate:fresh --seed` the **first** time (or whenever `DB_SEED=true`);
   on later restarts it only applies pending migrations, so **your data is kept**.
6. Starts the server with `php artisan serve`.

The SQLite database and seeded/uploaded images live on named Docker volumes
(`sqlite-data`, `public-storage`), so they survive `docker compose down`. Use
`docker compose down -v` to reset everything.

Dish and floor-plan images are read by the seeders from the repository's
`docs/images/` folder, which is mounted read-only into the container.

---

## Run locally without Docker

If you prefer a native setup, an onboarding script handles everything (copies
`.env`, installs Composer + npm dependencies, links storage, migrates, seeds and
starts the dev server).

**Requirements:** PHP 8.5, Composer, Node.js 22+ and npm.

```bash
# macOS / Linux
./onboarding.sh

# Windows (PowerShell)
./onboarding.ps1
```

The dev server (Laravel + Vite) starts via `composer run dev`. The local default
database is configured in `.env` — copy `.env.example` to `.env` first if the
script reports it is missing.

---

## Running tests

This project uses [Pest](https://pestphp.com/).

```bash
# With Docker
docker compose exec app php artisan test --compact

# Locally
php artisan test --compact
```

---

## Troubleshooting

- **Port 8000 already in use** — start with a different host port:
  `APP_PORT=9000 docker compose up -d`.
- **Permission denied on `docker` commands (Linux)** — add yourself to the
  `docker` group (`sudo usermod -aG docker "$USER"`) and log out/in, or prefix
  commands with `sudo`.
- **Changes to PHP/Blade not showing** — rebuild: `docker compose up --build`.
  (Front-end assets are compiled into the image at build time.)
- **Start over from scratch** — `docker compose down -v && docker compose up --build`
  wipes the database and re-seeds.
