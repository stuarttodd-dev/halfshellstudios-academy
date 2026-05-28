# Order layers so dependencies cache and code copies last

**Course page:** [Order layers for cache](https://docker.learnio.dev/learn/sections/chapter-dockerfile-core/dockerfile-core-order-layers-for-cache)

Worked example for chapter 3 — two Dockerfiles with the same PHP app and Monolog dependency, different layer order, so you can verify cache behaviour on source vs lockfile changes.

## Ordering rule

Put rare, expensive work above noisy source changes:

```text
FROM → RUN (apt/extensions) → COPY composer.json composer.lock → RUN composer install → COPY . .
```

| Changes rarely | Changes often |
| -------------- | ------------- |
| Base image | Application PHP files |
| Apt / extensions | README, tests, assets |
| `composer.lock` | |

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile.bad`](Dockerfile.bad) | `COPY . .` before `composer install` — any file edit busts deps |
| [`Dockerfile`](Dockerfile) | Copy lockfiles only, install deps, then `COPY . .` |
| [`composer.json`](composer.json) / [`composer.lock`](composer.lock) | Realistic Composer layer |
| [`src/index.php`](src/index.php) | Edit this to test source-only cache |
| [`.dockerignore`](.dockerignore) | Keeps `vendor/`, `.git`, and docs out of context |
| [`demo.sh`](demo.sh) | First build, edit source, rebuild both, check CACHED |
| [`clean.sh`](clean.sh) | Remove demo image tags |

Build from **this directory**.

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
./clean.sh
```

## Verify cache yourself (lesson steps)

First build:

```bash
docker build -f Dockerfile -t cache-order:good .
```

Edit a PHP file only:

```bash
echo '<?php echo "edited\n";' > src/index.php
docker build --progress=plain -f Dockerfile -t cache-order:good .
```

Confirm the `composer install` step shows **CACHED**.

Edit `composer.lock` (or change a version in `composer.json` and run `composer update`), rebuild, and confirm **composer install reruns**.

Compare with the bad Dockerfile:

```bash
docker build -f Dockerfile.bad -t cache-order:bad .
# edit src/index.php again
docker build --progress=plain -f Dockerfile.bad -t cache-order:bad .
```

On `:bad`, `composer install` should run again even when only source changed.

## Related examples

| Lesson | Folder |
| ------ | ------ |
| RUN / COPY / WORKDIR discipline | [run-copy-workdir-demo](../run-copy-workdir-demo/) |
| history + layer dedup (chapter 2) | [history-size-layer-dedup](../../2-images-layers/history-size-layer-dedup/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| First build is slow | Expected — compare **second** build after a small source edit |
| `:good` rebuilds Composer after source edit | Ensure only `src/` changed, not `composer.json` / `composer.lock` |
| `COPY failed: file not found` for `composer.lock` | Run `composer update` in this folder or copy a lock file in |
| Context too large | Build from this folder; check `.dockerignore` |

## Lesson acceptance

- First builds of `:bad` and `:good` succeed
- Source-only edit keeps `composer install` **CACHED** on `:good`
- Same edit reruns Composer on `:bad`
- Lockfile change reruns Composer on `:good`

## What you proved

- Layer order matches **change frequency**, not reading order
- `COPY composer.json composer.lock` before `COPY . .` saves CI time on everyday edits
- `.dockerignore` keeps the late broad copy narrow

← [Chapter 3 — Dockerfile core](../README.md)
