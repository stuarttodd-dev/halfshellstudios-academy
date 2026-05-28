# A .env-driven Laravel-style container

**Course page:** [A .env-driven Laravel-style container](https://docker.learnio.dev/learn/sections/chapter-env-secrets/env-secrets-a-dotenv-driven-laravel-style-container)

Same image, different environment at **container start** — package the app in the image; inject Laravel-shaped config with `env_file` and Compose `environment`.

```text
image (artifact)  +  env_file / environment  →  getenv() / config()  →  no rebuild for DB_HOST
```

## Three `.env` files (do not mix them up)

| File | Who reads it | Committed? |
| ---- | ------------ | ---------- |
| `.env` (project root) | Compose `${VAR}` interpolation in YAML | Usually no |
| `.env.docker` | Container process via `env_file:` | No |
| `.env.example` / `.env.docker.example` | Humans | Yes |

## Quick demo

```bash
chmod +x demo.sh clean.sh
cp .env.example .env
cp .env.docker.example .env.docker   # or use committed .env.docker
./demo.sh
```

## Try it manually

```bash
docker compose build
docker compose run --rm app php print-env.php

# Edit .env.docker — APP_ENV=staging; re-run without rebuild
docker compose run --rm app php print-env.php

docker compose run --rm -e APP_ENV=production app php print-env.php
docker compose run --rm app printenv DB_HOST APP_ENV
```

## Compose shape

[`compose.yaml`](compose.yaml) loads [`.env.docker`](.env.docker) into `app` (Laravel runtime keys) and uses project [`.env`](.env) only for `${DB_PASSWORD}` / `${DB_DATABASE}` on `db`. `DB_HOST: db` in `environment:` pins the service name from chapter 6 and overrides `env_file` for that key (lesson 8.5).

## Dockerfile rules

| Do | Don't |
| -- | ----- |
| `ENV LOG_CHANNEL=stderr` safe defaults | `ENV DB_HOST=…` per environment |
| Inject runtime via `env_file` | `COPY .env` into the image |

[`.dockerignore`](.dockerignore) lists `.env`.

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`print-env.php`](print-env.php) | Stand-in for Laravel `env()` / `config()` |
| [`Dockerfile`](Dockerfile) | Minimal CLI image — no baked secrets |
| [`compose.yaml`](compose.yaml) | `app` + `db` with `env_file` |
| [`demo.sh`](demo.sh) | Build once, change env without rebuild |
| [`clean.sh`](clean.sh) | `docker compose down -v` |

## Laravel mapping

| Key | Laptop | Compose `app` container |
| --- | ------ | --------------------- |
| `DB_HOST` | `127.0.0.1` | `db` (service name) |
| `REDIS_HOST` | `127.0.0.1` | `redis` |
| `APP_URL` | `http://localhost:8000` | Published edge URL |

## Related

| Lesson | Folder |
| ------ | ------ |
| Build vs run time | [build-vs-runtime-demo](../build-vs-runtime-demo/) |
| Compose precedence | Course lesson 8.5 |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `127.0.0.1` for DB inside Compose | Use `DB_HOST=db`, not localhost |
| Env change ignored on running container | `docker compose up -d --force-recreate app` |
| `.env` in image layers | `.dockerignore`, rebuild, `docker history` (8.4, 8.7) |
| Compose `${VAR}` empty | Create project `.env` from `.env.example` |

← [Chapter 8 — Env and secrets](../README.md)
