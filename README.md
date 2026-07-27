# RestaurantApp

A restaurant management system — reservations, table & order management, kitchen
and bar displays, menu/ingredient management, maintenance tasks and statistics —
built with **Laravel 12 + Livewire 4**.

It ships as **MolvenoCLI** (`molveno`): a single, self-contained binary that
installs Docker and runs the whole application in a container. The application is
bundled inside the binary, so the executable is all you need — no PHP, Node or
.NET required.

## Installation

Download the `molveno` binary for your platform from the
[**latest release**](https://github.com/spectrum-capgemini/Team-404-Name-Not-Found/releases/latest),
then follow the steps for your OS. Afterwards, `molveno install` sets up Docker
(if needed) and `molveno run` starts the app at <http://localhost:8000>.

### Windows

Download `molveno-win-x64.exe`. In **PowerShell**, from the folder you saved it in:

```powershell
# Rename it to molveno.exe (and optionally move it somewhere on your PATH)
Rename-Item .\molveno-win-x64.exe molveno.exe

.\molveno.exe install    # installs Docker Desktop if it isn't already
.\molveno.exe run        # build and start the app
```

### macOS

Download `molveno-osx-arm64` (Apple Silicon). In **Terminal**:

```bash
chmod +x molveno-osx-arm64                                    # make it executable
xattr -d com.apple.quarantine molveno-osx-arm64 2>/dev/null || true   # clear the Gatekeeper "downloaded" flag
sudo mv molveno-osx-arm64 /usr/local/bin/molveno              # put it on your PATH

molveno install    # installs Docker Desktop if it isn't already
molveno run        # build and start the app
```

### Linux

Download `molveno-linux-x64`. In a **terminal**:

```bash
chmod +x molveno-linux-x64                          # make it executable
sudo mv molveno-linux-x64 /usr/local/bin/molveno    # put it on your PATH

molveno install    # installs Docker Engine if it isn't already
molveno run        # build and start the app
```

> No prebuilt binary for your architecture? Build one with the .NET 10 SDK —
> see [`Molveno/README.md`](Molveno/README.md).

## Usage

| Command | Description |
|---------|-------------|
| `molveno install` | Install the Docker requirements for your OS. |
| `molveno run` | Build and start the app at <http://localhost:8000>. Options: `--env=<path>` (custom env file), `--port=<n>` (different host port). |
| `molveno stop` | Stop and remove the running app container. Data is kept. |
| `molveno env` | Write an example `.env` file you can edit and pass to `molveno run --env=<path>`. |

Full command reference: [`Molveno/README.md`](Molveno/README.md).

### Demo logins

The app seeds one account per role; **all accounts use the password `password`**:

| Role | Email |
|------|-------|
| Manager | `manager@demo.com` |
| Server | `server@demo.com` |
| Chef | `chef@demo.com` |
| Bartender | `bartender@demo.com` |
| Receptionist | `receptionist@demo.com` |
| Barista | `barista@demo.com` |
| Maintenance | `maintenance@demo.com` |

## Repository layout

| Path | What it is |
|------|------------|
| [`RestaurantApp/`](RestaurantApp/) | The Laravel application and its Docker setup. |
| [`Molveno/`](Molveno/) | The MolvenoCLI (`molveno`) source and build scripts. |
| [`docs/`](docs/) | Dish and floor-plan images used by the database seeders. |

Prefer to run it without the CLI? RestaurantApp can also be started directly with
Docker Compose — see [`RestaurantApp/README.md`](RestaurantApp/README.md).
