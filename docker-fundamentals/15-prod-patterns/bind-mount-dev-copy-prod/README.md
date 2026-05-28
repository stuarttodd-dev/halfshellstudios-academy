# Bind-mount in dev, copy-only in prod

**Course page:** [Bind-mount in dev, copy-only in prod](https://docker.learnio.dev/learn/sections/chapter-prod-patterns/prod-patterns-bind-mount-in-dev-copy-only-in-prod)

Development bind-mounts source for instant feedback. Production runs code from the image (`COPY` in the Dockerfile) — not the host checkout.

```text
compose.dev.yaml  →  ./public:/var/www/html/public  (live edits)
compose.prod.yaml →  image: ch15-bind:local         (no source mount)
```

## File ownership

| File | Role |
| ---- | ---- |
| [`compose.yaml`](compose.yaml) | Shared graph — **no** application source mounts |
| [`compose.dev.yaml`](compose.dev.yaml) | `build:` + bind mount `./public` |
| [`compose.prod.yaml`](compose.prod.yaml) | `image:` only (simulates CI digest deploy) |
| [`Dockerfile`](Dockerfile) | `COPY public` — prod code path |

Data volumes (MySQL, `storage/`) still belong in base or prod; this lesson is about **application source**, not durable data.

## Quick demo

```bash
chmod +x demo.sh clean.sh
cp .env.example .env   # optional HTTP_PORT
./demo.sh
```

Proves: dev sees `version: edited-live` without rebuild; prod still serves `version: initial` from the image built before the edit.

## Try it manually

```bash
docker compose -f compose.yaml -f compose.dev.yaml build
docker compose -f compose.yaml -f compose.dev.yaml config | grep -E 'source:|target:' | head -20
docker compose -f compose.yaml -f compose.prod.yaml config | grep -E 'source:.*public' || echo 'no app source bind mount in prod merge'

docker compose -f compose.yaml -f compose.dev.yaml up -d
curl -s http://127.0.0.1:8080/
# edit public/index.php, curl again

docker compose -f compose.yaml -f compose.dev.yaml down
docker compose -f compose.yaml -f compose.prod.yaml up -d
curl -s http://127.0.0.1:8080/   # still initial until you rebuild + redeploy image
```

## Review checklist (real repos)

- [ ] No `.:/var/www/html` in `compose.yaml` or `compose.prod.yaml`
- [ ] `compose.dev.yaml` owns the source bind mount
- [ ] Dockerfile `COPY` includes everything prod needs except durable data
- [ ] `docker compose -f compose.yaml -f compose.prod.yaml config` shows no app-code bind mount

## Related

| Lesson | Folder |
| ------ | ------ |
| compose.yaml + dev/prod overrides | [compose-dev-prod-overrides](../compose-dev-prod-overrides/) |
| Code in image, data in volume | Chapter 5 |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Prod serves code you never built | Prod merge still bind-mounts `.` — run `compose ... config` |
| Dev edits ignored | Wrong mount path or running prod stack without `compose.dev.yaml` |
| Cannot “remove” base bind mount in prod | Move mount out of `compose.yaml` (merge adds volumes; rarely removes) |
| `vendor/` mismatch | Host `vendor/` masks image — run `composer install` in container for dev |

← [Chapter 15 — Prod patterns](../README.md)
