# BI Portal

Laravel portal for managing and displaying Power BI/Fabric reports, datasets,
workspaces, users, groups, subscriptions and capacities.

## Local installation

Requirements: Docker Desktop with Docker Compose.

```bash
git clone https://github.com/edmenn/bireports.git
cd bireports
cp .env.example .env
./install.sh
```

`install.sh` starts the local Docker services, installs PHP and Node
dependencies, generates `APP_KEY` when needed, runs migrations and seeders, and
builds the frontend assets. The default host mapping is port 80:

```text
http://localhost
```

To use another host port, set `APP_PORT` in `.env` before running the installer.
The database hostname inside Docker is `pgsql`; do not replace it with
`127.0.0.1` for the Compose installation.

Useful local commands:

```bash
./install.sh --sync       # sync dependencies, migrations and caches
./install.sh --refresh    # rebuild the app container and recreate services
./install.sh --help       # show all options, including the destructive --fresh
docker compose down       # stop the local environment
```

### What the scripts do

The scripts are wrappers around the Compose files; users do not need to run
the individual `composer`, `npm` or `php artisan` installation commands by
hand.

| Script | Use | Important options / behavior |
| --- | --- | --- |
| `install.sh` | Local development installation and synchronization using `docker-compose.yml`. | No arguments runs the interactive menu. `--sync` skips Node and synchronizes dependencies, migrations and caches. `--refresh` rebuilds and recreates the app services. `--refresh-nobuild` recreates without rebuilding. `--seed` reruns seeders. `--fresh` deletes all local database tables and reseeds them. |
| `deploy.sh` | Production deployment using `docker-compose.secure.yml`. | No arguments runs the interactive menu. `--sync` copies code and clears caches. `--sync-migrate` also runs migrations. `--refresh` rebuilds the production image and repopulates the protected code volume. `--refresh-nobuild` restarts without rebuilding. `--cleanup` runs Docker's safe system prune without deleting volumes. |
| `verify.sh` | Checks the production stack. | It may start missing production services, verifies extensions and writable paths, runs migrations and checks the configured HTTPS domain. It is not a local installer. |
| `scripts/run-scheduler.sh` | Process used by the `scheduler` Compose service. | Runs Laravel scheduled tasks every 60 seconds. Do not run it on the host. |
| `scripts/run-queue-worker.sh` | Process used by the `queue_worker` Compose service. | Runs the Laravel queue worker. Do not run it on the host. |
| `scripts/sync-lang-assets.sh` | Copies language image assets into `public/lang`. | It is called by `install.sh`; it is normally not needed manually. |

Do not use `--fresh` on a database with data you need. Do not use the local
`install.sh` against the production stack; use `deploy.sh` there.

The local seeder creates this development administrator:

```text
User: admin@birentable.com
Password: XH6cdURvPXk2uOhTm3K8
```

These credentials are for local development only. Change or remove them before
using the application outside a local environment.

## Power BI/Fabric configuration

Power BI/Fabric credentials are stored encrypted in the application's
`configurations` table, not in `.env`. Configure them from the administrator
configuration screen or with Artisan:

```bash
docker compose exec birentable php artisan powerbi:credentials:set \
  --tenant="DIRECTORY_TENANT_ID" \
  --client="APPLICATION_CLIENT_ID" \
  --secret="CLIENT_SECRET_VALUE" \
  --activate
```

Do not commit the secret value or put it in the README. The scope used for Power
BI tokens is configured as:

```text
https://analysis.windows.net/powerbi/api/.default
```

Before configuring the portal, read the tenant and permissions guide for the
appropriate language:

- [Microsoft Fabric permissions and setup guide (English)](BI_Portal_Permisos_Fabric_EN.pdf)
- [Guía de permisos y configuración de Microsoft Fabric (Español)](BI_Portal_Permisos_Fabric_ES.pdf)

The guides describe the Entra application, Graph consent, Fabric tenant
settings, workspace access and Azure permissions. Microsoft may change the
administrative interface, so validate the final permissions in the target
tenant.

## Testing

Run the Laravel test suite inside the application container:

```bash
docker compose exec birentable composer test
```

The repository regression check is:

```bash
bash .agents/skills/bi-portal-regression-guard/scripts/check-invariants.sh
```

## Production deployment

The production stack is separate from the local stack and is defined in
`docker-compose.secure.yml`. Review `.env`, DNS, TLS and backups before using:

```bash
./deploy.sh
```

`deploy.sh` builds the production image, populates the protected code volume,
runs migrations and starts Caddy, Nginx, the application, scheduler, queue and
PostgreSQL. Do not use `--fresh` on a database containing real data.

Useful production options are shown by:

```bash
./deploy.sh --help
```

## Repository scripts

- `install.sh`: local Docker installation and synchronization.
- `deploy.sh`: production Docker deployment and synchronization.
- `verify.sh`: production environment verification.
- `scripts/run-scheduler.sh`: Laravel scheduler process.
- `scripts/run-queue-worker.sh`: Laravel queue worker process.

## License

No license is declared in this repository. Do not infer licensing terms from
this README.
