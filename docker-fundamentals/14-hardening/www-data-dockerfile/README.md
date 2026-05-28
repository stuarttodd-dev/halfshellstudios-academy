# USER www-data in your Dockerfile

**Course page:** [USER www-data in your Dockerfile](https://docker.learnio.dev/learn/sections/chapter-hardening/hardening-user-www-data-in-your-dockerfile)

Worked example for chapter 14 — run PHP-FPM as **`www-data`**, not root: `chown` writable paths as root, then `USER www-data`.

## Pattern

| As root | As `www-data` |
| ------- | --------------- |
| `apt-get`, extensions, `chown` | FPM workers, app runtime |
| `COPY` + fix ownership | Read code, write `storage/` |

```dockerfile
RUN chown -R www-data:www-data storage
USER www-data
CMD ["php-fpm"]
```

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | Lab image with `chown` + `USER www-data` |
| [`Dockerfile.broken`](Dockerfile.broken) | Same without `chown` — fails on `touch` |
| [`Dockerfile.full-example`](Dockerfile.full-example) | Lesson’s full Composer/FPM pattern |
| [`write-test.sh`](write-test.sh) | Prints `id`, writes `storage/logs/`, runs `php-fpm -t` |
| [`compose.example.yaml`](compose.example.yaml) | When `user:` overrides matter |
| [`demo.sh`](demo.sh) | Broken vs fixed build + verify |
| [`clean.sh`](clean.sh) | Remove demo images |

## Quick demo

```bash
chmod +x demo.sh clean.sh write-test.sh
./demo.sh
```

## Try it (manual)

```bash
docker run --rm php:8.3-fpm-bookworm id          # uid=0 baseline

docker build -f Dockerfile.broken -t ch14-www-data:broken .
docker run --rm ch14-www-data:broken             # Permission denied

docker build -f Dockerfile -t ch14-www-data .
docker image inspect ch14-www-data --format 'Config.User={{.Config.User}}'
docker run --rm --entrypoint id ch14-www-data
docker run --rm ch14-www-data

docker run --rm --user 0:0 --entrypoint id ch14-www-data   # override regression
```

## Verify on a running container

```bash
docker build -t ch14-www-data .
docker run -d --name ch14-app --entrypoint sleep ch14-www-data infinity
docker exec ch14-app id
docker exec ch14-app ./write-test.sh
docker rm -f ch14-app
```

## Related

| Lesson | Folder |
| ------ | ------ |
| PHP-FPM from scratch (also uses `USER www-data`) | [php-fpm-from-scratch](../../3-dockerfile-core/php-fpm-from-scratch/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `Permission denied` on `storage/` | `chown` before `USER`; check bind mount host uid |
| `composer` / `apt` fails after `USER` | Move those `RUN` lines above `USER` |
| `Config.User` empty | Add explicit `USER www-data` — do not rely on entrypoint alone |
| Compose `user: "0:0"` | Removes hardening — dev break-glass only |

## Lesson acceptance

- Broken build fails write as `www-data`; fixed build succeeds
- Image `Config.User` is `www-data` (or `33`)
- You can explain root-for-build vs www-data-for-run

## What you proved

- Non-root runtime limits damage from a compromised worker
- Ownership on writable paths must be fixed **before** `USER`

← [Chapter 14 — Hardening](../README.md)
