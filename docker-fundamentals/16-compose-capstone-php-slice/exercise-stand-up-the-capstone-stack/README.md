# capstone-php-slice

Six-service PHP slice: nginx, PHP-FPM, MySQL, Redis, worker, and scheduler — wired with Compose overlays for dev and production-shaped checks.

**Course solution (16.10):** [Solution: stand up the capstone stack](https://docker.learnio.dev/learn/sections/chapter-compose-capstone-php-slice/compose-capstone-php-slice-solution-stand-up-the-capstone-stack)

**Matching exercise (16.9):** [Exercise: stand up the capstone stack](https://docker.learnio.dev/learn/sections/chapter-compose-capstone-php-slice/compose-capstone-php-slice-exercise-stand-up-the-capstone-stack)

Both lessons point at this folder (`github_example: 16-compose-capstone-php-slice/exercise-stand-up-the-capstone-stack`).

## Quick start

```bash
cp .env.example .env
./solution-demo.sh
```

Or the shorter smoke-only path:

```bash
cp .env.example .env
./demo.sh
```

Or manually:

```bash
cp .env.example .env
make smoke
```

Uses `docker compose -f compose.yaml -f compose.dev.yaml --env-file .env`.

## Project layout

```text
.
├── compose.yaml           # six-service base graph
├── compose.dev.yaml       # build, bind mounts, TLS, xdebug
├── compose.prod.yaml      # release image, read-only PHP services
├── docker/
│   ├── php/Dockerfile     # build / runtime / dev stages
│   ├── nginx/default.conf # HTTP + dev HTTPS
│   └── mysql/init/        # schema + seed
├── app/public/            # index, healthz, db-redis probe
├── artisan                # worker/scheduler stub
├── Makefile               # dev, smoke, prod-config, ci-compose
└── .env.example
```

## Production-shaped config check

Build the runtime image, then validate the prod merge:

```bash
docker build --target runtime -f docker/php/Dockerfile -t capstone-php-slice:release .
make ci-compose
make prod-config
```

## Dev TLS certs

Generated automatically by `demo.sh`, or manually:

```bash
mkdir -p docker/nginx/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/certs/dev.key \
  -out docker/nginx/certs/dev.crt \
  -subj "/CN=localhost"
```

## What you proved

- Compose wires six roles with healthchecks and named volumes
- Dev overlay adds bind mounts and HTTPS without forking the base file
- `make smoke` exercises HTTP, MySQL, Redis, FPM, worker, and scheduler in one pass

← [Chapter 16](../README.md) · [Docker fundamentals](../../README.md)
