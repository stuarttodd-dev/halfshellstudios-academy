# Compose two-service stack (+ MySQL, Redis, Mailhog)

Chapter 7 examples in [`compose-two-service`](.) (per course manifest).

| Lesson | Compose file |
| ------ | ------------- |
| [nginx and PHP-FPM in Compose](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-nginx-and-php-fpm-in-compose) | [`compose.yaml`](compose.yaml) |
| [Add MySQL, Redis, and Mailhog with profiles](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-add-mysql-redis-mailhog-with-profiles) | [`compose.stack.yaml`](compose.stack.yaml) |

---

## Add MySQL, Redis, and Mailhog with profiles

**Course page:** [Add MySQL, Redis, and Mailhog with profiles](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-add-mysql-redis-mailhog-with-profiles)

Extends the two-service stack with **db**, **redis**, and optional **mailhog** (`profiles: ["mail"]`).

### Stack files

| Item | Purpose |
| ---- | ------- |
| [`compose.stack.yaml`](compose.stack.yaml) | nginx, php, db, redis, mailhog (profile `mail`) |
| [`.env.stack.example`](.env.stack.example) | DB/Redis/Mailhog env defaults |
| [`public/stack.php`](public/stack.php) | Shows `DB_HOST`, `REDIS_HOST`, `MAIL_HOST` env |
| [`up-stack.sh`](up-stack.sh) | Lean stack (no Mailhog) |
| [`up-stack-mail.sh`](up-stack-mail.sh) | Opt in: `--profile mail` |
| [`down-stack.sh`](down-stack.sh) | Tear down (includes mail profile) |
| [`smoke-stack.sh`](smoke-stack.sh) | curl + mysql + redis checks |

### Quick demo

```bash
chmod +x up-stack.sh up-stack-mail.sh down-stack.sh smoke-stack.sh
./up-stack.sh
./smoke-stack.sh

./up-stack-mail.sh    # adds Mailhog UI on :8025 (set MAILHOG_PORT if busy)
curl -I http://127.0.0.1:8025/
./down-stack.sh
```

### Profiles

```bash
# Default: nginx, php, db, redis — no Mailhog
docker compose -f compose.stack.yaml up -d

# Optional mail catcher UI
docker compose -f compose.stack.yaml --profile mail up -d
```

Mailhog is useful sometimes, not mandatory noise on every `up`.

---

## nginx and PHP-FPM in Compose

**Course page:** [nginx and PHP-FPM in Compose](https://docker.learnio.dev/learn/sections/chapter-compose-basics/compose-basics-nginx-and-php-fpm-in-compose)

The smallest PHP-shaped stack: **nginx** (public) in front of **php** (internal), one `compose.yaml`, shared code volume, FastCGI by service name.

## What this proves

| Idea | How |
| ---- | --- |
| Multiple services | `nginx` + `php` in one file |
| One public entry point | Host `8080` → nginx only |
| Internal service | `php` has no `ports:` — reachable as `php:9000` on the Compose network |
| Service-name DNS | `fastcgi_pass php:9000` in nginx config |
| Shared code | Both services mount `./` at `/var/www/html` |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | Two-service stack |
| [`docker/nginx/default.conf`](docker/nginx/default.conf) | Routes `.php` to `php:9000` |
| [`public/index.php`](public/index.php) | Plain-text smoke response |
| [`.env.example`](.env.example) | Optional `HTTP_PORT` (default `8080`) |
| [`up.sh`](up.sh) / [`down.sh`](down.sh) | Start and stop the stack |
| [`smoke.sh`](smoke.sh) | `curl` the public entry point |

## Quick start

```bash
chmod +x up.sh down.sh smoke.sh
./up.sh
./smoke.sh
./down.sh
```

## Try it (manual)

From this folder:

```bash
docker compose up -d
docker compose ps
curl http://127.0.0.1:8080/
```

Expected output includes:

```text
nginx + PHP-FPM via Compose
php_version=8.3.x
hostname=<php container id>
```

The `hostname` line comes from the **php** container — proof nginx forwarded the request over the internal network.

Inspect wiring:

```bash
docker compose exec nginx wget -qO- http://127.0.0.1/ 2>/dev/null | head -1
docker compose exec php printenv HOSTNAME
```

## Architecture

```text
Browser → host:8080 → nginx:80 → fastcgi php:9000 → public/index.php
```

Only **nginx** publishes a host port. PHP-FPM stays on the default Compose network.

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `bind: address already in use` on 8080 | Copy `.env.example` to `.env` and set `HTTP_PORT=8083` (or any free port), then `docker compose up -d` again |
| `502 Bad Gateway` | Ensure `php` is up: `docker compose ps`; check `fastcgi_pass php:9000` matches the service name |
| Blank page / file download | `SCRIPT_FILENAME` must match `root`; here `root` is `/var/www/html/public` |
| `curl` connection refused | Use `127.0.0.1:8080` — mapping is bound to localhost in this example |
| Mailhog port 8025 in use | Set `MAILHOG_PORT=8027` in `.env` when using `--profile mail` |
| Mailhog slow on Apple Silicon | Image uses `platform: linux/amd64` for compatibility |

## Lesson acceptance

- `docker compose up -d` starts **nginx** and **php**
- `curl http://127.0.0.1:8080/` returns the PHP response
- You can explain why `php` does not need a host `ports:` entry

## What you proved

- Compose can declare a multi-service stack instead of many `docker run` commands
- The public edge is nginx; PHP-FPM is internal and addressed by **service name**

← [Chapter 7 — Compose basics](../README.md)
