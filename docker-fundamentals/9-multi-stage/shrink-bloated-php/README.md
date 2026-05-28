# Shrink a bloated PHP image

**Course page:** [Shrink a bloated PHP image](https://docker.learnio.dev/learn/sections/chapter-multi-stage/multi-stage-shrink-a-bloated-php-image)

Compare a **single-stage** PHP-FPM image that keeps Composer, Git, and unzip in the final layer with a **multi-stage** build that copies only `vendor/` into runtime.

```text
Dockerfile.bloated  →  one FROM, build tools still in the tag
Dockerfile.slim     →  composer:lts AS deps  →  COPY --from=deps vendor/
```

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Builds both tags, prints `docker image ls` sizes, and checks the slim image has no `composer`/`git` in runtime.

## Try it manually

```bash
docker build -f Dockerfile.bloated -t ch9-php:bloated .
docker build -f Dockerfile.slim -t ch9-php:slim .
docker image ls ch9-php:bloated ch9-php:slim
docker history ch9-php:bloated | head
docker history ch9-php:slim | head
```

## What's in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile.bloated`](Dockerfile.bloated) | Single-stage — apt + Composer in final image |
| [`Dockerfile.slim`](Dockerfile.slim) | `deps` + `runtime` — production pattern (lesson 9.3) |
| [`composer.json`](composer.json) | Small real dependency (`monolog/monolog`) |
| [`demo.sh`](demo.sh) | Build both; assert slim &lt; bloated |

## What stays out of runtime

| In `deps` only | In `runtime` |
| -------------- | ------------ |
| `composer` binary | `php-fpm` |
| Git, unzip | `vendor/` + `public/` |
| Composer cache | App autoload via `vendor/` |

Do not `COPY --from=deps /app` into runtime — copy `vendor/` explicitly (lesson 9.3).

## Related

| Lesson | Folder |
| ------ | ------ |
| Composer deps → FPM runtime | Course lesson 9.3 |
| COPY --from named stages | [copy-from-named-stages](../copy-from-named-stages/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `composer.lock` missing | Run `composer install` locally or let `demo.sh` generate it |
| Slim not smaller | Rebuild with `--no-cache`; compare same PHP base (8.3 bookworm) |
| App missing extensions | Install `pdo_mysql` etc. in `runtime` stage only |

← [Chapter 9 — Multi-stage](../README.md)
