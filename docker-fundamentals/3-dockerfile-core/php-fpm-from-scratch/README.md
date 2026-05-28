# PHP-FPM Dockerfile from scratch

**Course page:** [PHP-FPM Dockerfile from scratch](https://docker.learnio.dev/learn/sections/chapter-dockerfile-core/dockerfile-core-php-fpm-dockerfile-from-scratch)

Worked example for chapter 3 — a single-stage PHP-FPM image with extensions, Composer, Laravel-shaped writable paths, and a non-root runtime user.

## What’s in this folder

| Path | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | PHP-FPM 8.3 on Bookworm, extensions, Composer install, `www-data` |
| [`composer.json`](composer.json) / [`composer.lock`](composer.lock) | Dependency manifest (Monolog for a realistic `vendor/` layer) |
| [`app/`](app/) | Small PSR-4 app code |
| [`public/index.php`](public/index.php) | Entry script (use behind nginx FastCGI in a full stack) |
| [`storage/`](storage/) | Writable path (Laravel-style) |
| [`bootstrap/cache/`](bootstrap/cache/) | Writable path (Laravel-style) |
| [`.dockerignore`](.dockerignore) | Keeps `vendor/` out of the build context |

Build from **this directory** (the folder that contains the `Dockerfile`).

## Read the Dockerfile

```dockerfile
FROM php:8.3-fpm-bookworm
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
# RUN apt + docker-php-ext-install (intl, opcache, pdo_mysql, zip)
COPY composer.json composer.lock ./
RUN composer install --no-dev ...
COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm"]
```

| Piece | Why it matters |
| ----- | -------------- |
| `COPY --from=composer:2` | Composer binary without curl-install in your layer |
| `COPY composer.json composer.lock` before app source | Reuse dependency layer when only PHP files change |
| `chown` before `USER www-data` | Only root can fix ownership on writable dirs |
| `CMD ["php-fpm"]` | Exec form; FPM listens on port 9000 |

## Build and inspect

From this folder:

```bash
docker build -t myapp:0.1 .
docker run --rm myapp:0.1 php -m
docker run --rm myapp:0.1 composer --version
docker run --rm myapp:0.1 php -r "echo extension_loaded('pdo_mysql') ? 'pdo_mysql ok' : 'missing';"
```

| Check | What you should see |
| ----- | ------------------- |
| `docker build` | Success; `composer install` and extension compile steps |
| `php -m` | `intl`, `pdo_mysql`, `zip`, `Zend OPcache`, etc. |
| `composer --version` | Composer 2.x |
| `pdo_mysql` probe | `pdo_mysql ok` |

Optional — run the entry script (FPM images also include CLI `php`):

```bash
docker run --rm myapp:0.1 php public/index.php
```

You should see `php-fpm-from-scratch`.

## Cache behaviour when app code changes

Edit `app/Demo.php`, rebuild with plain progress:

```bash
docker build --progress=plain -t myapp:0.2 .
```

Expect `FROM`, the extension `RUN`, and `composer install` to often show `CACHED`; `COPY . .` reruns when application files change.

## What this example deliberately leaves out

- Multi-stage builds (later chapters)
- A `HEALTHCHECK` (add only when the probe is trustworthy)
- Extra packages (`curl`, `vim`) without a clear job

## Troubleshooting

### `composer install` fails during build

Confirm `composer.json` and `composer.lock` are in the build context and paths match the `COPY` line.

### `docker-php-ext-install` errors

The Debian packages in the `RUN` block must match the extensions you install (`libicu-dev` for `intl`, `libzip-dev` for `zip`, etc.).

### Permission errors on `storage/` at runtime

Confirm `chown` runs **before** `USER www-data` and paths match your framework layout.

### Huge slow build context

Build from this folder only; [`.dockerignore`](.dockerignore) should exclude `vendor/` and `.git/`.

## Lesson acceptance

- Image builds from this directory
- Required extensions appear in `php -m`
- `composer` is available in the image
- `pdo_mysql` probe prints `pdo_mysql ok`
- You can explain why `composer.json` copies before `COPY . .`

## What you proved

- A PHP-FPM Dockerfile can install OS deps, compile extensions, install Composer deps, copy app code, fix ownership, and run as non-root
- Layer order targets rebuild speed on common code edits

← [Chapter 3 — Dockerfile core](../README.md)
