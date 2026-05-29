# Compose inner loop (exercise + solution)

| Lesson | Use |
| ------ | --- |
| [Exercise: compose inner loop](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-exercise-compose-inner-loop) | Work through the commands below before running `demo.sh` |
| [Solution: compose inner loop](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-solution-compose-inner-loop) | [`demo.sh`](demo.sh) — same sequence with checks |

Chapter 4 habits at project scope: validate the merged model, bring the stack up, smoke-test HTTP, read logs, run one-offs, tear down cleanly.

## The loop

```text
config → up -d → ps → curl → logs → top → run --rm → exec → stop/start → down
```

## Stack

Services `web`, `php`, and `db` in [`compose.yaml`](compose.yaml). `web` serves the default nginx welcome page on `127.0.0.1:8080` (override with `.env`).

## Exercise

Complete these steps yourself (course page has the full checklist):

```bash
docker compose config --quiet && echo "config OK"
docker compose config --services

docker compose up -d
docker compose ps

curl -sS -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8080/

docker compose logs --tail=15 web
docker compose logs --tail=15 php

docker compose top web
docker compose top php

docker compose run --rm php php -r 'echo "one-off ok\n";'
docker compose exec php php -r 'echo "exec ok\n";'

docker compose stop php
docker compose ps
docker compose ps -a
docker compose start php

docker compose down
docker compose ps -a
```

## Solution

Automated walkthrough (matches the course solution lesson):

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

`demo.sh` uses host port **8080** by default. If it is busy, the script picks the next free port and prints which one it chose. Override with `cp .env.example .env` and set `HTTP_PORT`.

## Checklist

- [ ] `docker compose config --quiet` succeeds
- [ ] `docker compose ps` shows `web`, `php`, and `db` running after `up`
- [ ] `curl` against `127.0.0.1:8080` returns an HTTP status, not connection refused
- [ ] `docker compose logs` shows output for at least `web` or `php`
- [ ] `docker compose run --rm php …` prints `one-off ok`
- [ ] `docker compose exec php …` prints `exec ok`
- [ ] `docker compose ps -a` showed `php` exited while stopped
- [ ] `docker compose down` removed the stack

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | Three-service stack (`web`, `php`, `db`) |
| [`demo.sh`](demo.sh) | Solution: full compose inner loop with assertions |
| [`clean.sh`](clean.sh) | `docker compose down -v` |
| [`.env.example`](.env.example) | Optional `HTTP_PORT`, `DB_PASSWORD` |

## Related

| Lesson | Folder |
| ------ | ------ |
| compose config, ps, logs, and run | [compose-config-ps-logs-run](../compose-config-ps-logs-run/) |
| Dev overrides | [compose-override-dev](../compose-override-dev/) |
| CLI inner loop (chapter 4) | [exercise-cli-inner-loop](../../4-cli-workflow/exercise-cli-inner-loop/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Port 8080 in use | `export HTTP_PORT=8081`, or use `.env`, or let `demo.sh` pick a port |
| `config` fails on undefined volume | Top-level `volumes:` block is already in `compose.yaml` |
| `php` exits on start | Remove/rename any `compose.override.yaml` with `build: .` and no Dockerfile |
| `curl` connection refused | Match port in `compose.yaml` and `curl` URL; `docker compose ps` — `web` must be `Up` |
| `exec` fails | Service must be running from `up`; use `run` for one-offs |
| `db` slow on first boot | Wait for MariaDB init; `docker compose logs db` |

← [Chapter 7 — Compose basics](../README.md)
