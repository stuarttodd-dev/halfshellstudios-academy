# compose.yaml plus dev and prod overrides

**Course page:** [compose.yaml plus dev and prod overrides](https://docker.learnio.dev/learn/sections/chapter-prod-patterns/prod-patterns-compose-yaml-plus-dev-and-prod-overrides)

Worked example for chapter 15 — one **base** service graph, **dev** deltas (bind mounts, `build:`), **prod** deltas (pinned image, `read_only`, no source mounts).

## File ownership

| File | Owns |
| ---- | ---- |
| [`compose.yaml`](compose.yaml) | Shared graph: `web`, `app`, `db`, healthchecks, `db_data` |
| [`compose.dev.yaml`](compose.dev.yaml) | Dev: `build`, bind mounts, `APP_DEBUG` |
| [`compose.prod.yaml`](compose.prod.yaml) | Prod: `image`, `read_only`, `tmpfs`, `APP_ENV=production` |

Later `-f` files win on scalar keys. **Do not** put dev bind mounts in the base file and try to remove them in prod.

## Merge commands

```bash
# Dev
docker compose -f compose.yaml -f compose.dev.yaml up -d --build

# Prod model (verify before deploy)
docker compose -f compose.yaml -f compose.prod.yaml config

# Default stack via env
export COMPOSE_FILE=compose.yaml:compose.dev.yaml
docker compose config --services
```

## Quick demo

```bash
chmod +x demo.sh down.sh
cp .env.example .env   # optional
./demo.sh
./down.sh
```

Or use the [Makefile](Makefile):

```bash
make dev
make prod-config
make down
```

## Try it (lesson checks)

```bash
docker compose -f compose.yaml -f compose.dev.yaml config --services
docker compose -f compose.yaml -f compose.prod.yaml config --services

docker compose -f compose.yaml -f compose.dev.yaml config | grep -E 'read_only:|/usr/share/nginx/html' || true
docker compose -f compose.yaml -f compose.prod.yaml config | grep -E 'read_only:|/var/www/html' || true

echo '<h1>ch15 dev</h1>' > html/index.html
docker compose -f compose.yaml -f compose.dev.yaml up -d
curl -s http://127.0.0.1:8080 | head -1
```

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | Base stack |
| [`compose.dev.yaml`](compose.dev.yaml) | Dev overrides |
| [`compose.prod.yaml`](compose.prod.yaml) | Prod overrides |
| [`Dockerfile`](Dockerfile) | Dev `build:` / local `ch15-app:prod-demo` for `config` |
| [`html/index.html`](html/index.html) | Static page for dev web mount |
| [`Makefile`](Makefile) | `make dev`, `make prod-config` |
| [`demo.sh`](demo.sh) | Config diff + dev live-edit curl |

Replace `APP_IMAGE_PROD` with your registry digest in real deploys (chapter 13).

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Prod still serves old code | Prod merge still has bind mounts — check `compose ... config` |
| `build:` in prod config | Prod override must set `image:` (and build `ch15-app:prod-demo` locally for this demo) |
| Port 8080 busy | Set `HTTP_PORT` in `.env` |
| `COMPOSE_FILE` ignored | Use `compose.yaml:compose.dev.yaml` on Unix; same shell as `docker compose` |

## Lesson acceptance

- Same `--services` list for dev and prod merges
- Dev merge mounts `./html`; prod merge has `read_only` and no app/html bind mount
- Editing `html/index.html` updates curl output without rebuild

## What you proved

- One graph, environment-specific deltas — not two full compose forks
- `docker compose ... config` is the merged truth before you trust a deploy

← [Chapter 15 — Prod patterns](../README.md)
