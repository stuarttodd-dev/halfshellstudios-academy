# Run as www-data, not root

**Course page:** [Run as www-data, not root](https://docker.learnio.dev/learn/sections/chapter-php-images/php-images-run-as-www-data-not-root)

Build and `chown` as root. Run PHP-FPM as **`www-data`** (uid 33 on Debian bookworm). Verify with `id` and a real write to `storage/`.

```text
RUN chown storage  →  USER www-data  →  CMD php-fpm
```

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

## Try it manually

```bash
docker build -t ch10-user:local .
docker run --rm --entrypoint id ch10-user:local
docker run --rm ch10-user:local php public/index.php
docker run --rm ch10-user:local cat storage/logs/ch10-write-test.log
docker run --rm ch10-user:local php-fpm -t
docker run --rm ch10-user:local sh -c 'test "$(id -u)" -eq 33 && echo non-root OK'
```

Broken compare:

```bash
docker build -f Dockerfile.broken -t ch10-user:broken .
docker run --rm ch10-user:broken php -r "file_put_contents('storage/logs/x.log','t');"
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | `chown` + `USER www-data` |
| [`Dockerfile.broken`](Dockerfile.broken) | Missing `chown` — write fails |
| [`public/index.php`](public/index.php) | Writes log as runtime user |
| [`demo.sh`](demo.sh) | Good vs broken + verification |

## Compose gotcha

Do not set `user: "0:0"` in production when the Dockerfile sets `USER www-data` — it undoes non-root runtime.

## Related

| Lesson | Folder |
| ------ | ------ |
| Extensions on bookworm | [fpm-extensions-composer](../fpm-extensions-composer/) (`Dockerfile.bookworm` also uses `USER www-data`) |
| Deeper hardening | [14-hardening/www-data-dockerfile](../../14-hardening/www-data-dockerfile/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `Permission denied` on `storage/` | `chown -R www-data:www-data` before `USER` |
| `id` shows root in Compose | Remove `user:` override |
| `php-fpm -t` fails after `USER` | Ensure pool config readable by `www-data` |

← [Chapter 10 — PHP images](../README.md)
