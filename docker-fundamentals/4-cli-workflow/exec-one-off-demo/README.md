# docker exec and one-off commands

**Course page:** [docker exec and one-off commands](https://docker.learnio.dev/learn/sections/chapter-cli-workflow/cli-workflow-docker-exec-and-one-off-commands)

Worked example for chapter 4 — `docker exec` on a **named running** container vs `docker run --rm` for **disposable** one-offs.

## Two approaches

| Approach | New container? | Needs running target? | Use for |
| -------- | -------------- | --------------------- | ------- |
| `docker exec` | No | Yes | Inspect config, env, quick command in `ch4-web` |
| `docker run --rm …` | Yes | No | `composer install`, migrations, scripts |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`hello.php`](hello.php) | Mounted into a one-off `php` container |
| [`demo.sh`](demo.sh) | Lesson flow: exec, one-offs, stopped-container error |
| [`clean.sh`](clean.sh) | Remove `ch4-web` |
| [`.env.example`](.env.example) | `HTTP_PORT` if 8080 is busy |

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
./clean.sh
```

## Try it (manual)

### Named service (revisit with exec)

```bash
docker run -d --name ch4-web -p 127.0.0.1:8080:80 nginx:stable-alpine
curl -I http://127.0.0.1:8080/

docker exec ch4-web nginx -t
docker exec ch4-web printenv NGINX_VERSION
docker exec -it ch4-web sh    # ls /etc/nginx/conf.d, then exit
docker ps --filter name=ch4-web   # still running
```

### One-off throwaways

```bash
docker run --rm alpine sh -c 'echo built for $(uname -m); id'
docker run --rm php:8.3-cli-alpine php -r 'echo PHP_VERSION, "\n";'
docker run --rm -v "$(pwd):/app" -w /app php:8.3-cli-alpine php hello.php
```

Composer-shaped one-off (no long-lived app container needed):

```bash
docker run --rm -v "$(pwd):/app" -w /app composer:2 composer --version
```

### Exec needs a running container

```bash
docker stop ch4-web
docker exec ch4-web nginx -v    # fails — not running
docker start ch4-web
docker exec ch4-web nginx -v
```

Clean up:

```bash
docker stop ch4-web && docker rm ch4-web
```

## Habits

| Container | Flags | Why |
| --------- | ----- | --- |
| `ch4-web` | `-d --name ch4-web` | Logs, exec, stop — you revisit it |
| Composer / script job | `--rm` (often no `--name`) | Exit and disappear |

**Exec teaches what broke; Dockerfile and deploy fix it for good** — do not `apt install` in prod and call it done.

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `container … is not running` | `docker start ch4-web` or check `docker ps -a` |
| `No such container` | Typo in name; match `docker ps -a` exactly |
| `the input device is not a TTY` | Drop `-t` for piped input, or use `-i` only |
| Name already in use | `docker rm -f ch4-web` if disposable |
| One-off cannot see project files | Add `-v "$(pwd):/app" -w /app` |
| Port 8080 in use | Set `HTTP_PORT` in `.env` |

## Lesson acceptance

- `docker exec ch4-web nginx -t` succeeds while nginx is running
- `docker run --rm` one-offs leave no stopped containers
- `docker exec` fails after `docker stop ch4-web`
- You can explain when to use exec vs `run --rm`

## What you proved

- Same container vs new container is the main CLI fork
- Name services you debug; let one-offs vanish with `--rm`

← [Chapter 4 — CLI workflow](../README.md)
