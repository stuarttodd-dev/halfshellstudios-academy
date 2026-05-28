# Smoke the image in CI before push

**Course page:** [Smoke the image in CI before push](https://docker.learnio.dev/learn/sections/chapter-ci-registry/ci-registry-smoke-the-image-in-ci-before-push)

A green `docker build` is not enough. Load the image on the runner, run a cheap smoke command, **then** push tags.

```text
build + load  →  docker run smoke  →  push tags + digest
```

## What smoke catches

| Catches | Does not replace |
| ------- | ---------------- |
| Wrong/missing `CMD` | Full integration tests |
| Missing copied files | Load testing |
| `php -v` / script smoke | Trivy gate (13.7) |
| `php-fpm -t` on FPM images | curl through nginx |

## Quick demo (local)

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Builds good image → smoke passes → push to local registry. Also builds `Dockerfile.broken` and shows smoke fails before push.

## Try it manually

```bash
docker build -t ch13-smoke:local .
docker run --rm ch13-smoke:local
docker run --rm ch13-smoke:local && echo "smoke passed, ok to push"

docker build -f Dockerfile.broken -t ch13-smoke:broken .
docker run --rm ch13-smoke:broken || echo "broken — do not push"
```

## GitHub Actions reference

[`.github/workflows/smoke-before-push.yml`](.github/workflows/smoke-before-push.yml):

1. `build-push-action` with `push: false`, `load: true`
2. `docker run` smoke steps
3. Second build with `push: true` (GHA cache reuse)

Copy to your app repo root alongside `Dockerfile` and `smoke.php`.

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | Good image — `php smoke.php` |
| [`Dockerfile.broken`](Dockerfile.broken) | `CMD ["false"]` — smoke fails |
| [`smoke.php`](smoke.php) | Smoke output `ch13 smoke ok` |
| [`demo.sh`](demo.sh) | Local build → smoke → push sequence |
| [`smoke-before-push.yml`](.github/workflows/smoke-before-push.yml) | CI reference workflow |

## Related

| Lesson | Folder |
| ------ | ------ |
| GitHub Actions build and push | [github-actions-build-and-push](../github-actions-build-and-push/) |
| Tag with SHA / pin digest | [tag-with-git-sha-and-pin-prod-to-digest](../tag-with-git-sha-and-pin-prod-to-digest/) |

← [Chapter 13 — CI and registry](../README.md)
