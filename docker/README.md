# Docker deployment (EC2)

This folder contains container runtime files for production deployment with Docker Compose.

## Services

- web: Nginx + PHP-FPM serving Laravel.
- worker: Queue worker (`php artisan queue:work`).
- scheduler: Scheduler worker (`php artisan schedule:work`).
- mysql: MySQL 8.4 database.
- redis: Redis 7 cache/broker.

## Quick start

1. Copy `.env.docker.example` to `.env` and adjust secrets (`APP_URL`, `DOCKER_DB_PASSWORD`, `DOCKER_DB_ROOT_PASSWORD`).
2. Build and start containers:

```bash
docker compose up -d --build
```

3. Generate app key (first deploy):

```bash
docker compose run --rm web php artisan key:generate --show
```

4. Put the generated key into `.env` as `APP_KEY=...` and restart `web`.

5. No manual migration command is required: on `web` start the container runs migrations and seeders automatically.

Default access credentials are:

- Admin: `admin@cartera.local` / `Admin2026#`
- Analyst: `analista@cartera.local` / `Analista2026#`

You can override these with `APP_ADMIN_*` and `APP_ANALYST_*` variables in `.env`.

## Notes

- `RUN_MIGRATIONS` and `RUN_SEEDERS` are enabled by default in the docker setup.
- Uploads and runtime files persist in `storage_data` Docker volume.
- Expose port 80 in your EC2 security group.
