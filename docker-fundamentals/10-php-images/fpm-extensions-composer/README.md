# Official PHP-FPM image plus extensions

| Lesson | Course page |
| ------ | ----------- |
| 10.3 — one extension | [Official image plus one extension](https://docker.learnio.dev/learn/sections/chapter-php-images/php-images-official-image-plus-one-extension) |
| 10.4 — common mix | [Install pdo_mysql, redis, intl, gd](https://docker.learnio.dev/learn/sections/chapter-php-images/php-images-install-pdo-mysql-redis-intl-gd) |

Extend a pinned `php:*-fpm` tag with `docker-php-ext-install` (bundled) and `pecl install` + `docker-php-ext-enable` (PECL). Prove the result with `php -m` before shipping.

| File | Lesson |
| ---- | ------ |
| [`Dockerfile`](Dockerfile) | 10.3 — `php:8.3-fpm-alpine` + `pdo_mysql` |
| [`Dockerfile.bookworm`](Dockerfile.bookworm) | 10.4 — extensions + `USER www-data` (10.7) |

## Quick demo (lesson 10.3)

```bash
chmod +x demo.sh demo-bookworm.sh clean.sh
./demo.sh
```

## Try it manually

```bash
docker build -t ch10-pdo:0.1 .
docker run --rm ch10-pdo:0.1 php -v
docker run --rm ch10-pdo:0.1 php -m | grep -E '^pdo'
docker run --rm ch10-pdo:0.1 php -r "var_dump(extension_loaded('pdo_mysql'));"
docker run --rm ch10-pdo:0.1 php-fpm -t

docker run --rm php:8.3-fpm-alpine php -m | grep -i pdo_mysql || echo "no pdo_mysql on vanilla base"
```

## Quick demo (lesson 10.4)

```bash
./demo-bookworm.sh
```

## Try it manually (lesson 10.4)

```bash
docker build -f Dockerfile.bookworm -t app-php:exts .
docker run --rm app-php:exts php -m | grep -E 'pdo_mysql|intl|gd|redis'
docker run --rm app-php:exts php-fpm -t
```

### Bundled vs PECL (this Dockerfile)

| Extension | Source | Install path |
| --------- | ------ | -------------- |
| `pdo_mysql`, `intl`, `gd` | Bundled | `docker-php-ext-install` (configure `gd` first) |
| `redis` | PECL | `pecl install redis` then `docker-php-ext-enable redis` |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`demo.sh`](demo.sh) | Build Alpine + `pdo_mysql`, verify modules and FPM |
| [`demo-bookworm.sh`](demo-bookworm.sh) | Build bookworm extension bundle |
| [`clean.sh`](clean.sh) | Remove `ch10-*` demo images |

## Helpers (official image)

| Script | Use |
| ------ | --- |
| `docker-php-ext-install` | Bundled extensions (`pdo_mysql`, `intl`, …) |
| `docker-php-ext-configure` | Flags before install (`gd`, …) |
| `docker-php-ext-enable` | After `pecl install` |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `docker-php-ext-install: not found` | Not an official `php` image |
| `phpize` / autoconf errors on Alpine | Include `$PHPIZE_DEPS` in `.build-deps` |
| Extension missing after build | Rebuild with `--no-cache` once |
| `apt-get` in Alpine Dockerfile | Use `apk`, or switch to bookworm |
| `php-fpm: not found` | Use `php:*-fpm`, not `cli` variant |

## Related

| Lesson | Folder |
| ------ | ------ |
| Run as www-data, not root | [run-as-www-data](../run-as-www-data/) |

← [Chapter 10 — PHP images](../README.md)
