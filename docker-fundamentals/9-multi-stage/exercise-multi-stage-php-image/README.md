# Exercise / Solution: multi-stage PHP image

| Lesson | Course page |
| ------ | ----------- |
| Exercise 9.9 | [Exercise: multi-stage PHP image](https://docker.learnio.dev/learn/sections/chapter-multi-stage/multi-stage-exercise-multi-stage-php-image) |
| Solution 9.10 | [Solution: multi-stage PHP image](https://docker.learnio.dev/learn/sections/chapter-multi-stage/multi-stage-solution-multi-stage-php-image) |

Chapter checkpoint: **`deps`** builds `vendor/` with Composer; **`runtime`** is PHP-FPM with `pdo_mysql` + app — bridge only `vendor/`, not the whole `/app` from deps.

```text
composer:lts AS deps  →  COPY --from=deps /app/vendor  →  php:8.3-fpm-bookworm AS runtime
```

## Exercise

Work from [`Dockerfile.starter`](Dockerfile.starter) until the checklist passes, then compare to [`Dockerfile`](Dockerfile).

## Solution demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Runs exercise tasks 1–8: `deps` build, `--target runtime`, `php public/index.php`, `php -m`, no composer, `php-fpm -t`, optional single-stage size compare.

## Try it manually

```bash
docker build --target deps -t ch9-exercise:deps .
docker build --target runtime -t ch9-exercise:release .
docker run --rm ch9-exercise:release php public/index.php
docker run --rm ch9-exercise:release php -m | grep pdo_mysql
docker run --rm ch9-exercise:release sh -c 'command -v composer || echo "no composer in runtime"'
docker run --rm ch9-exercise:release php-fpm -t
```

## File tree

| File | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | Reference solution (`deps` + `runtime`) |
| [`Dockerfile.starter`](Dockerfile.starter) | Incomplete starter for the exercise |
| [`Dockerfile.single`](Dockerfile.single) | Optional bloated baseline (task 9) |
| [`src/Demo.php`](src/Demo.php) | Checks `pdo_mysql` + Monolog |
| [`.dockerignore`](.dockerignore) | Excludes host `vendor/` |

## Checklist

- [ ] `deps` uses `composer:lts` and lockfile-first `COPY`
- [ ] `runtime` installs `pdo_mysql` and `opcache`
- [ ] `COPY --from=deps /app/vendor ./vendor` (not entire `/app`)
- [ ] `docker build --target runtime -t ch9-exercise:release .`
- [ ] `php public/index.php` prints `pdo_mysql ok, monolog ok`
- [ ] No `composer` binary in release
- [ ] `php-fpm -t` passes

## Related

| Lesson | Folder |
| ------ | ------ |
| Composer deps → FPM runtime | Course lesson 9.3 |
| Shrink bloated image | [shrink-bloated-php](../shrink-bloated-php/) |
| COPY --from named stages | [copy-from-named-stages](../copy-from-named-stages/) |

← [Chapter 9 — Multi-stage](../README.md)
