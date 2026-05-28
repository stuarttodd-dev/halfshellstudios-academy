# compose config, ps, logs, and run

**Course page:** [compose config, ps, logs, and run](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-compose-config-ps-logs-and-run)

Daily operator toolkit: read the merged model, inspect running containers, follow one service's logs, run one-off tasks.

```text
config → ps → top → logs -f <service> → run --rm <service> …
```

## Five commands

| Command | Answers |
| ------- | ------- |
| `docker compose config` | Merged YAML after overrides + `.env` |
| `docker compose ps` / `ps -a` | Project container state |
| `docker compose top` | Processes inside containers |
| `docker compose logs -f <service>` | One service output stream |
| `docker compose run --rm <service> …` | One-off task container |

## Quick demo

```bash
chmod +x demo.sh clean.sh
cp .env.example .env   # optional
./demo.sh
```

Or via [Makefile](Makefile):

```bash
make config
make up
make ps
make logs-php
make down
```

## Try it manually

```bash
docker compose config --quiet && echo "config OK"
docker compose config --services
docker compose up -d
docker compose ps
docker compose top php
docker compose logs --tail=20 web
docker compose run --rm php php -r 'echo "one-off ok\n";'
docker compose exec php php -r 'echo "exec ok\n";'
docker compose stop php && docker compose ps -a
docker compose down
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | Base `web`, `php`, `db` stack |
| [`compose.override.yaml`](compose.override.yaml) | Dev bind mount + `APP_DEBUG` |
| [`demo.sh`](demo.sh) | Lesson debugging ladder |
| [`Makefile`](Makefile) | Shortcuts for `config`, `ps`, `logs` |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `config` fails | Fix YAML; check top-level `volumes:` |
| Wrong merge in `config` | Run from this directory; check `COMPOSE_FILE` |
| `ps` empty | Wrong project directory / `COMPOSE_PROJECT_NAME` |
| `top` missing | Use `docker compose exec php ps aux` |
| `exec` fails | Service must be running; use `run` for one-offs |

## Related

| Lesson | Folder |
| ------ | ------ |
| Dev overrides | [compose-override-dev](../compose-override-dev/) |
| Two-service stack | [compose-two-service](../compose-two-service/) |

← [Chapter 7 — Compose basics](../README.md)
