# RUN, COPY, and WORKDIR discipline

**Course page:** [RUN, COPY, and WORKDIR discipline](https://docker.learnio.dev/learn/sections/chapter-dockerfile-core/dockerfile-core-run-copy-and-workdir-discipline)

Worked example for chapter 3 — compare a deliberately sloppy Dockerfile with a disciplined one: early `WORKDIR`, `COPY` instead of `ADD`, and chained `RUN` lines.

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile.bad`](Dockerfile.bad) | Split `apt-get` layers, absolute `COPY` paths, `ADD .` |
| [`Dockerfile`](Dockerfile) | `WORKDIR`, chained apt `RUN`, relative `COPY`, exec-form `CMD` |
| [`composer.json`](composer.json) | Minimal Composer project for `composer install` |
| [`src/index.php`](src/index.php) | App file copied in the final `COPY . .` |
| [`.dockerignore`](.dockerignore) | Keeps build context small |
| [`demo.sh`](demo.sh) | Build both images, compare history, run the good image |
| [`clean.sh`](clean.sh) | Remove demo tags |

Build from **this directory** so paths and context stay correct.

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
./clean.sh
```

## Compare manually

Sloppy build:

```bash
docker build -f Dockerfile.bad -t workdir-bad:0.1 .
docker image history workdir-bad:0.1 --no-trunc | head -8
```

Disciplined build:

```bash
docker build -t workdir-good:0.1 .
docker image history workdir-good:0.1 | head -8
docker run --rm workdir-good:0.1
```

On `:bad` you should see separate `apt-get update` and `apt-get install` layers. On `:good`, one chained apt `RUN`, short relative `COPY` lines, and a predictable `/var/www/html` working directory.

## Habits this lesson teaches

| Instruction | Discipline |
| ----------- | ---------- |
| `WORKDIR` | Set the app root once, early; use relative `COPY` after it |
| `COPY` vs `ADD` | Default to `COPY`; use `ADD` only when you mean extract or remote fetch |
| `RUN` | One concern per line; chain `apt-get update`, install, and `rm -rf /var/lib/apt/lists/*` in the same layer |

See also [static-site-six-line](../static-site-six-line/) (`WORKDIR` + `COPY`) and [php-fpm-from-scratch](../php-fpm-from-scratch/) (full PHP-FPM image from lesson 3.4).

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `COPY failed: file not found` | Run `docker build` from this folder; check filename and `.dockerignore` |
| Context too large | Do not build from a parent directory; keep context to this app folder |
| Apt `Unable to locate package` after cache | Merge `apt-get update` and `install` into one `RUN`; rebuild with `--no-cache` once |
| `composer install` in wrong directory | Add or fix `WORKDIR` before the `RUN` |
| Reviewer asks why `ADD` | Replace with `COPY` unless you need archive extraction |

## Lesson acceptance

- You can explain why `:bad` has extra apt layers and scattered paths
- `:good` builds with `WORKDIR /var/www/html` and relative `COPY composer.json ./`
- `docker run --rm workdir-good:0.1` prints PHP version via exec-form `CMD`

## What you proved

- `WORKDIR` anchors relative paths for reviewers and for `RUN` / `COPY`
- Chained `RUN` avoids orphan apt layers and stale index cache surprises
- `COPY` is the default; `ADD` needs a deliberate reason

← [Chapter 3 — Dockerfile core](../README.md)
