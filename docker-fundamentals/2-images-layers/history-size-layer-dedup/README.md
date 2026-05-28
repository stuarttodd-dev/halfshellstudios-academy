# docker history, image size, and layer dedup

**Course page:** [docker history, image size, and layer dedup](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-docker-history-image-size-layer-dedup)

Worked example for chapter 2 — two PHP Dockerfiles with the same app, different layer order, so you can compare rebuild behaviour, image history, and shared base layers.

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile.bad`](Dockerfile.bad) | Copies the whole project before installing dependencies — cache busts on any file change |
| [`Dockerfile.good`](Dockerfile.good) | Installs Composer deps from `composer.json` only, then copies app source last |
| [`composer.json`](composer.json) | Small Monolog dependency for a realistic `composer install` layer |
| [`src/index.php`](src/index.php) | One-line PHP file you edit to invalidate cache |

## First build

From this folder:

```bash
docker build -f Dockerfile.bad -t php-layers:bad .
docker build -f Dockerfile.good -t php-layers:good .
docker image ls php-layers
```

| Image | Dockerfile pattern |
| ----- | ------------------ |
| `php-layers:bad` | `COPY . .` before `RUN` (apt + Composer) |
| `php-layers:good` | `composer.json` only, then `COPY . .` last |

Note image sizes. The bad image is not always larger on disk, but rebuild behaviour diverges after you edit source.

## Change one file and rebuild

```bash
echo "<?php echo 'hi v2';" > src/index.php

time docker build -f Dockerfile.bad -t php-layers:bad .
time docker build -f Dockerfile.good -t php-layers:good .
```

Watch build output:

| Image | After editing `src/index.php` |
| ----- | ----------------------------- |
| `:bad` | `COPY . .` and the following `RUN` execute again (apt and Composer work repeat) |
| `:good` | Only the final `COPY . .` rebuilds; Composer layer should show `CACHED` |

You have understood the lesson when a one-line PHP change rebuilds Composer on `:bad` but stays cached on `:good`.

## Read the history

```bash
docker image history --no-trunc php-layers:good
docker image history --no-trunc php-layers:bad
```

Each row is a layer. Large `SIZE` on a `RUN composer install` line is a signal that instruction order or cache misses are costing you.

On `:bad` after the source edit, find the large `RUN` layer that repeated apt and Composer work.

## Layer deduplication across images

Both Dockerfiles use `FROM php:8.3-cli-bookworm`. Pull the base and list images:

```bash
docker pull php:8.3-cli-bookworm
docker image ls php
```

After building both variants, they share the `FROM php:8.3-cli-bookworm` layers on disk when the base digest matches. Registries apply the same idea: identical layer digests are stored once and referenced from many manifests.

## Try it yourself

1. Build `:bad` and `:good`; record wall-clock time for each first build
2. Edit `src/index.php` only
3. Rebuild both; compare which steps re-execute in the build log
4. Run `docker image history --no-trunc` on both tags
5. Find the largest layer on `:bad` after the source edit

## Troubleshooting

### First build is slow

Both variants download packages and run `composer install` on the first build. That is expected. The lesson comparison matters on the **second** build after a small source change.

### `:good` rebuilds Composer after a source edit

Check that only `src/index.php` changed, not `composer.json`. If you changed dependencies, the Composer layer should rebuild.

### `composer.lock` missing

`Dockerfile.good` uses `composer.lock*` (optional). Without a lock file, Composer resolves versions on each fresh build. For learning, that is fine.

## Lesson acceptance

- First builds of `:bad` and `:good` both succeed
- Editing `src/index.php` forces expensive layers to rebuild on `:bad`
- Same edit keeps the Composer `RUN` cached on `:good`
- `docker image history` shows which instruction created each layer

## What you proved

- Layer order controls **cache invalidation**, not just image size
- `docker history` shows which steps cost you on rebuild
- Shared base images deduplicate layers on disk (and in registries)

← [Chapter 2 — Images and layers](../README.md)
