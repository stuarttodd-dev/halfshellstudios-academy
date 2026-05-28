# Exercise / Solution: harden a PHP-FPM image

| Lesson | Course page |
| ------ | ----------- |
| **Solution 14.10** | [Solution: harden a PHP-FPM image](https://docker.learnio.dev/learn/sections/chapter-hardening/hardening-solution-harden-a-php-fpm-image) |
| Exercise 14.9 | [Exercise: harden a PHP-FPM image](https://docker.learnio.dev/learn/sections/chapter-hardening/hardening-exercise-harden-a-php-fpm-image) |

Both lessons point at this folder (`github_example: 14-hardening/exercise-harden-a-php-fpm-image`).

Chapter 14 checkpoint: non-root runtime, read-only root, tmpfs writable islands, `cap_drop: [ALL]`, and `docker run --read-only` verification.

```text
USER www-data  →  read_only + tmpfs + volume  →  cap_drop ALL  →  verify
```

## Starter vs solution

| File | Role |
| ---- | ---- |
| [`Dockerfile.starter`](Dockerfile.starter) + [`compose.starter.yaml`](compose.starter.yaml) | Soft baseline (root, no hardening) |
| [`Dockerfile`](Dockerfile) + [`compose.yaml`](compose.yaml) | Hardened reference |

## Writable paths at runtime

| Path | Mechanism | Persists container recreate? |
| ---- | --------- | ---------------------------- |
| `/tmp` | tmpfs | No |
| `/var/run` | tmpfs | No |
| `/var/www/html/bootstrap/cache` | tmpfs | No |
| `/var/www/html/storage` | named volume `app-storage` | Yes |
| Application code under `/var/www/html` | image (read-only root) | From image until redeploy |

## Quick demo (solution 14.10)

```bash
chmod +x demo.sh solution-demo.sh clean.sh
./solution-demo.sh
```

Verifies `www-data`, `ReadonlyRootfs=true`, tmp/volume writes, `cap_drop ALL`, FPM on 9000, and `docker run --read-only`. `./demo.sh` is the same script.

## Try it manually

```bash
docker compose build app
docker compose up -d
docker compose exec app id
docker compose exec app php-fpm -t
docker inspect $(docker compose ps -q app) --format 'ReadonlyRootfs={{.HostConfig.ReadonlyRootfs}}'
docker compose exec app sh -c 'touch /tmp/ch14 && touch storage/logs/ch14.log'
docker compose down -v
```

## Checklist

- [ ] `USER www-data` in Dockerfile; `id` shows uid 33
- [ ] `read_only: true` and `ReadonlyRootfs=true`
- [ ] tmpfs for `/tmp` and `/var/run`
- [ ] named volume for `storage/`
- [ ] `cap_drop: [ALL]` with FPM still running
- [ ] `php-fpm -t` succeeds
- [ ] `docker run --read-only` smoke passes

## Related

| Lesson | Folder |
| ------ | ------ |
| USER www-data | [www-data-dockerfile](../www-data-dockerfile/) |
| Run as www-data (ch 10) | [10-php-images/run-as-www-data](../../10-php-images/run-as-www-data/) |
| FPM healthcheck (optional) | [10-php-images/fpm-healthcheck](../../10-php-images/fpm-healthcheck/) |

← [Chapter 14 — Hardening](../README.md)
