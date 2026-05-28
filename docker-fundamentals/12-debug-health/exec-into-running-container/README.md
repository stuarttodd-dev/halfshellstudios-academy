# docker exec into a running container

**Course page:** [docker exec into a running container](https://docker.learnio.dev/learn/sections/chapter-debug-health/debug-health-docker-exec-into-a-running-container)

Layer 3 of the debug ladder: after `docker ps` (state) and `logs`, use **one-shot** `docker exec` to inspect a running container.

| Tool | New container? | Target running? |
| ---- | ---------------- | --------------- |
| `docker exec` | No | Yes |
| `docker run --rm …` | Yes | No |
| `docker attach` | No | Yes (PID 1 stdout — rare for PHP) |

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

## Try it manually

```bash
docker run -d --name ch12-app php:8.3-fpm-alpine
docker ps --filter name=ch12-app

docker exec ch12-app php -v
docker exec ch12-app php -m | head -20
docker exec ch12-app printenv HOSTNAME
docker exec ch12-app php-fpm -t
docker exec ch12-app sh -c 'ps aux | head -8'

docker inspect ch12-app --format 'entrypoint={{json .Config.Entrypoint}} cmd={{json .Config.Cmd}}'

docker stop ch12-app
docker exec ch12-app php -v   # expect: not running
docker start ch12-app

docker rm -f ch12-app
```

## Compose habit

```bash
docker compose -f compose.minimal.yaml up -d
docker compose -f compose.minimal.yaml exec app php -v
docker compose -f compose.minimal.yaml exec app printenv DB_HOST
docker compose -f compose.minimal.yaml down
```

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`demo.sh`](demo.sh) | Automated lesson walkthrough |
| [`clean.sh`](clean.sh) | Remove `ch12-*` demo containers |
| [`compose.minimal.yaml`](compose.minimal.yaml) | Tiny stack for `compose exec` |

## PHP-FPM checks (layer 3)

```bash
docker exec ch12-app php-fpm -t
docker exec ch12-app sh -c 'ps aux | grep php-fpm'
```

Layer 4 still needs `curl` through nginx for HTTP (lesson 11.7).

## Observe in prod, fix in the repo

Exec is for **read-only triage**. Edits and `apt install` inside a running container do not survive recreate — fix Dockerfile, Compose, or app code (lesson 12.5).

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `container is not running` | Layer 1 first — `docker ps`, `logs`, fix crash loop |
| `executable file not found` | Minimal image without `sh` (lesson 12.6) |
| `not a TTY` | Drop `-t` for scripts; use `-it` only when interactive |
| `php-fpm -t` OK but 502 | nginx / FastCGI path (lessons 11.3, 12.2) |

← [Chapter 12 — Debug and health](../README.md)
