# Exercise / Solution: extend the official PHP image

| Lesson | Course page |
| ------ | ----------- |
| Exercise 10.9 | [Extend the official PHP image](https://docker.learnio.dev/learn/sections/chapter-php-images/php-images-exercise-extend-the-official-php-image) |
| Solution 10.10 | [Solution: extend the official PHP image](https://docker.learnio.dev/learn/sections/chapter-php-images/php-images-solution-extend-the-official-php-image) |

Chapter 10 checkpoint: one deployable `php:8.3-fpm-bookworm` image with extensions, Composer `deps` stage, INI/pool config, `www-data`, and FPM health probe.

```text
composer:lts (deps)  →  vendor/  →  php:8.3-fpm-bookworm (runtime)
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | `deps` + `runtime` multi-stage |
| [`docker/php/zz-app.ini`](docker/php/zz-app.ini) | PHP limits (lesson 10.6) |
| [`docker/php-fpm/zzz-app-pool.conf`](docker/php-fpm/zzz-app-pool.conf) | Pool listen + pm |
| [`docker/php-fpm/zzz-health.conf`](docker/php-fpm/zzz-health.conf) | FPM ping for HEALTHCHECK |
| [`src/Demo.php`](src/Demo.php) | Extension self-check |
| [`.dockerignore`](.dockerignore) | Keeps host `vendor/` out of build context |

## Quick demo (solution)

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Builds `deps` and `release`, runs the 10.1 checklist, app smoke, INI check, and tier-2 FPM health.

## Try it manually

```bash
docker build --target deps -t ch10-extend:deps .
docker build --target runtime -t ch10-extend:release .

docker run --rm ch10-extend:release php -m | grep -E 'pdo_mysql|intl|gd|redis'
docker run --rm ch10-extend:release php-fpm -t
docker run --rm ch10-extend:release id
docker run --rm ch10-extend:release sh -c 'command -v composer || echo no composer'
docker run --rm ch10-extend:release php -r "echo ini_get('memory_limit'), PHP_EOL;"
docker run --rm ch10-extend:release php public/index.php
docker run --rm ch10-extend:release php --ini | grep zz-app

docker run -d --name ch10-extend-test ch10-extend:release
sleep 20
docker inspect ch10-extend-test --format '{{.State.Health.Status}}'
docker rm -f ch10-extend-test
```

Expected app output:

```text
ch10 exercise: pdo_mysql:ok, intl:ok, gd:ok, redis:ok
```

## Checklist

- [ ] `FROM php:8.3-fpm-bookworm` in `runtime`
- [ ] `pdo_mysql`, `intl`, `gd`, `redis` in `php -m`
- [ ] `php-fpm -t` passes on release image
- [ ] `id` is `www-data` (uid 33)
- [ ] `vendor/` from `deps` only; no Composer in release
- [ ] `zz-app.ini` loaded (`memory_limit=256M`)
- [ ] `HEALTHCHECK` uses FPM ping (`cgi-fcgi`)

## Related

| Lesson | Folder |
| ------ | ------ |
| Extensions on bookworm | [fpm-extensions-composer](../fpm-extensions-composer/) |
| Run as www-data | [run-as-www-data](../run-as-www-data/) |
| FPM healthcheck | [fpm-healthcheck](../fpm-healthcheck/) |

← [Chapter 10 — PHP images](../README.md)
