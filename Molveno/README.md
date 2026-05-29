# MolvenoCLI (`molveno`)

A small, self-contained command-line tool that installs Docker and runs the
[RestaurantApp](../RestaurantApp/) Laravel application in a container — without
needing PHP, Composer, Node **or even .NET** installed on the machine.

The **entire RestaurantApp is bundled inside the binary** (its Docker build
context plus the seed images), so the single `molveno` executable is all you
need to ship and run the app — no separate source checkout required.

It is built with **.NET 10 Native AOT**, so each platform gets a single native
binary (`molveno` / `molveno.exe`) with no runtime dependency.

```
molveno install      # install the Docker requirements for your OS
molveno run          # build + run RestaurantApp in Docker
molveno env          # write an example .env file you can customise
```

---

## Table of contents

- [Requirements](#requirements)
- [Building the binary](#building-the-binary)
- [Commands](#commands)
  - [`molveno install`](#molveno-install)
  - [`molveno run`](#molveno-run)
  - [`molveno stop`](#molveno-stop)
  - [`molveno env`](#molveno-env)
- [How `run` finds RestaurantApp](#how-run-finds-restaurantapp)
- [Typical workflow](#typical-workflow)
- [Notes](#notes)
- [Troubleshooting](#troubleshooting)

---

## Requirements

**To *use* the binary:** nothing. It is fully self-contained; only Docker is
needed to actually run the app (and `molveno install` can set that up).

**To *build* the binary:**

- [.NET SDK 10](https://dotnet.microsoft.com/download) or newer.
- A C toolchain for Native AOT linking:
  - **Linux:** `clang`, `zlib` dev headers (e.g. `sudo apt-get install clang zlib1g-dev`).
  - **macOS:** Xcode Command Line Tools (`xcode-select --install`).
  - **Windows:** the "Desktop development with C++" workload from Visual Studio
    (or Build Tools).

---

## Building the binary

The project lives in [`MolvenoCLI/`](MolvenoCLI/). Helper scripts auto-detect
your platform:

```bash
# Linux / macOS
./build.sh                 # builds for the current OS/arch
./build.sh linux-arm64     # or pass a specific RID
```

```powershell
# Windows
./build.ps1                # builds for the current architecture
./build.ps1 win-arm64
```

The resulting binary is written to:

```
MolvenoCLI/bin/Release/net10.0/<RID>/publish/molveno      (molveno.exe on Windows)
```

Supported runtime identifiers (RIDs): `linux-x64`, `linux-arm64`, `osx-x64`,
`osx-arm64`, `win-x64`, `win-arm64`.

> **The build embeds RestaurantApp.** A build-time step packs the
> `RestaurantApp/` Docker context and `docs/images/` into the binary, so build
> from a full repository checkout (both folders must sit next to `Molveno/`) and
> with `tar` available (present by default on Linux, macOS and Windows 10+). If
> the sources aren't found, the CLI still builds — just without the bundled app,
> in which case `molveno run` needs `--path`.

> **Native AOT cannot cross-compile.** A Linux machine produces only the Linux
> binary, macOS only the macOS binary, etc. To ship all three, build on each OS
> (for example, via a CI matrix). Once built, copy `molveno` somewhere on your
> `PATH` (e.g. `/usr/local/bin`) to call it from anywhere.

---

## Commands

Run `molveno help` (or just `molveno`) for the built-in usage summary.

### `molveno install`

Installs the Docker requirements for the current operating system:

| OS | What it does |
|----|--------------|
| **macOS** | Downloads `Docker.dmg`, mounts it, copies `Docker.app` to `/Applications`, and launches Docker Desktop. |
| **Windows** | Downloads `Docker Desktop Installer.exe` and runs it elevated (UAC) with `install --accept-license`. |
| **Linux** | Downloads and runs the official `get.docker.com` convenience script with `sudo` (installs Docker Engine, CLI, containerd, buildx and the compose plugin). |

If Docker is already installed it reports the status and does nothing.

| Option | Description |
|--------|-------------|
| `--dry-run` | Print the installer URL that *would* be used, then exit without downloading or installing. |

```bash
molveno install
molveno install --dry-run
```

### `molveno run`

Builds the RestaurantApp image and starts it as a Docker container. On first
boot the container creates and seeds a SQLite database; subsequent runs reuse it.
When it's ready, open **<http://localhost:8000>**.

| Option | Description |
|--------|-------------|
| `--env=<path>` | Use a custom environment file (**optional**; see [`molveno env`](#molveno-env)). Defaults to the bundled `.env.docker`, or a generated SQLite default if that's missing. |
| `--port=<n>` | Publish on a different host port (overrides `APP_PORT` in the env file; default `8000`). |
| `--path=<dir>` | Use a RestaurantApp directory **on disk** instead of the bundled copy (for development; see [below](#how-run-finds-restaurantapp)). |
| `--refresh` | Re-extract the bundled app over the cached copy. |
| `--no-build` | Skip rebuilding the image and start the existing one. |
| `--follow` | Stream the container logs after starting (Ctrl+C detaches; the container keeps running). |

```bash
molveno run                                  # default SQLite env, port 8000
molveno run --env=./my.env --port=9000       # custom env on port 9000
molveno run --no-build --follow              # restart fast and tail logs
```

Under the hood it runs a container named `molveno-restaurantapp` from the image
`molveno-restaurantapp:local`, with:

- `--env-file` pointing at the resolved env file,
- named volumes `molveno-sqlite` and `molveno-public-storage` (so the database
  and seeded images survive restarts),
- the repository's `docs/` folder mounted read-only at `/var/www/docs` (the
  source images the seeders read).

Useful follow-ups it prints:

```bash
docker logs -f molveno-restaurantapp     # view logs
molveno stop                             # stop and remove (see below)
```

### `molveno stop`

Stops and removes the running RestaurantApp container. The named Docker volumes
(database and images) are preserved, so a later `molveno run` keeps your data.

```bash
molveno stop
```

To delete the data too:

```bash
docker volume rm molveno-sqlite molveno-public-storage
```

### `molveno env`

Writes an example environment file that mirrors the SQLite configuration the
container uses. Edit it and pass it to `molveno run --env=<path>`.

| Option | Description |
|--------|-------------|
| `--output=<path>` | Destination file (default: `./.env.example`). A bare positional path also works: `molveno env ./my.env`. |
| `--force` | Overwrite the file if it already exists. |

```bash
molveno env                       # writes ./.env.example
molveno env --output=./my.env     # writes ./my.env
```

---

## How `run` finds RestaurantApp

By default `molveno run` uses the **bundled** copy of RestaurantApp embedded in
the binary: on first run it is unpacked to a per-version cache directory and
built from there. Resolution order:

1. `--path=<dir>` if given (a RestaurantApp working tree on disk).
2. The `MOLVENO_APP_DIR` environment variable.
3. The **bundled** app (default).
4. As a last resort (binaries built without the app embedded), by walking up
   from the current directory and the binary's location looking for a
   `RestaurantApp/` folder.

The bundled app is extracted to:

- `$MOLVENO_HOME/app/<version>/`  if `MOLVENO_HOME` is set, otherwise
- `<local-app-data>/molveno/app/<version>/` — e.g. `~/.local/share/molveno`
  (Linux), `~/Library/Application Support/molveno` (macOS),
  `%LOCALAPPDATA%\molveno` (Windows).

Use `molveno run --refresh` to re-unpack it, or `--path` to run a checkout
instead:

```bash
molveno run --path=/path/to/RestaurantApp
```

---

## Typical workflow

```bash
# 1. Build the CLI for your machine (once)
./build.sh
sudo cp MolvenoCLI/bin/Release/net10.0/linux-x64/publish/molveno /usr/local/bin/

# 2. Make sure Docker is available
molveno install

# 3. Run the app
molveno run
# → open http://localhost:8000

# (optional) customise configuration
molveno env --output=./my.env
#   ...edit ./my.env...
molveno run --env=./my.env
```

Demo logins for the app are documented in
[`RestaurantApp/README.md`](../RestaurantApp/README.md) (all accounts use the
password `password`).

---

## Notes

- The binary is a true single file. On Linux it links only against system
  `libc`/`libm` — no .NET runtime required.
- Colour output is disabled automatically when output is piped or when the
  `NO_COLOR` environment variable is set.
- `molveno run` rebuilds the image by default so code changes are picked up; use
  `--no-build` for a fast restart.

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| `Docker is not installed` | Run `molveno install`. |
| `The Docker daemon is not running` | Start Docker Desktop (macOS/Windows) or `sudo systemctl start docker` (Linux). |
| `Could not locate the RestaurantApp directory` | Pass `--path=/path/to/RestaurantApp` or set `MOLVENO_APP_DIR`. |
| `Bind for 0.0.0.0:8000 failed: port is already allocated` | Another container/app uses port 8000 — run with `--port=9000`, or stop the other one. |
| Build fails with a linker/`clang` error | Install the C toolchain listed under [Requirements](#requirements). |
