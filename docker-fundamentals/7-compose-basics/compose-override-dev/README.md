# Dev overrides with compose.override.yaml

**Course page:** [Dev overrides with compose.override.yaml](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-dev-overrides-with-compose-override-yaml)

Keep the shared stack in **`compose.yaml`**. Put laptop-only deltas in **`compose.override.yaml`** — Compose merges both automatically on `docker compose up` (no extra `-f` flags).

Chapter 15 uses explicit `compose.dev.yaml` / `compose.prod.yaml`; this lesson is the zero-flag local convention.

```text
compose.yaml  +  compose.override.yaml  →  docker compose config  →  up
```

## Quick demo

```bash
chmod +x demo.sh down.sh
cp .env.example .env   # optional
./demo.sh
./down.sh
```

## Try it manually

```bash
docker compose config | grep -E 'APP_DEBUG|/var/www/html'
docker compose up -d --build
curl -s http://127.0.0.1:8080/

# Edit public/index.php on the host — curl again (no rebuild)

docker compose down
mv compose.override.yaml compose.override.yaml.bak
docker compose config | grep -E 'APP_DEBUG|/var/www/html' || echo 'no override in rendered config'
mv compose.override.yaml.bak compose.override.yaml
```

## What belongs where

| `compose.yaml` (base) | `compose.override.yaml` (local) |
| --------------------- | ------------------------------- |
| `web`, `php`, `db` services | `build: .` on `php` |
| `dbdata` volume, `DB_HOST` | `.:/var/www/html` bind mount |
| Image pins everyone shares | `APP_DEBUG`, `LOG_LEVEL` |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | Shared stack (`ch7stack`) |
| [`compose.override.yaml`](compose.override.yaml) | Local dev merge (copy from example if gitignored) |
| [`compose.override.example.yaml`](compose.override.example.yaml) | Team template |
| [`Dockerfile`](Dockerfile) | `build:` target for `php` |
| [`public/index.php`](public/index.php) | Live-edit demo |
| [`demo.sh`](demo.sh) | `config`, up, curl, edit, base-only compare |
| [`down.sh`](down.sh) | `docker compose down -v` |

## `.gitignore` habit

```gitignore
compose.override.yaml
```

Commit `compose.override.example.yaml` instead when overrides are personal.

## Explicit `-f` (chapter 15)

```bash
docker compose -f compose.yaml -f compose.staging.yaml up
export COMPOSE_FILE=compose.yaml:compose.override.yaml
```

Later files in the list win on conflicting keys.

## Related

| Lesson | Folder |
| ------ | ------ |
| nginx + PHP-FPM in Compose | [compose-two-service](../compose-two-service/) |
| Prod/dev explicit files | [compose-dev-prod-overrides](../../15-prod-patterns/compose-dev-prod-overrides/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Override ignored | Filename must be `compose.override.yaml`; run from this directory |
| `config` surprises | `COMPOSE_FILE` or stray override file |
| Empty bind mount | Paths relative to compose file directory |
| Cannot remove base volume in override | Redesign base (lesson 15) |

← [Chapter 7 — Compose basics](../README.md)
