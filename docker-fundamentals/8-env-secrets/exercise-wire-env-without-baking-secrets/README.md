# Exercise / Solution: wire env without baking secrets

| Lesson | Course page |
| ------ | ----------- |
| Exercise 8.9 | [Wire env without baking secrets](https://docker.learnio.dev/learn/sections/chapter-env-secrets/env-secrets-exercise-wire-env-without-baking-secrets) |
| Solution 8.10 | [Solution: wire env without baking secrets](https://docker.learnio.dev/learn/sections/chapter-env-secrets/env-secrets-solution-wire-env-without-baking-secrets) |

Chapter 8 checkpoint: package the app in the image; inject credentials and environment at **container start** via `env_file` and Compose — not `Dockerfile` `ENV`, not `COPY .env`.

Use **fake secrets only** in local files (`not-a-real-app-key`, `not-a-real-db-pass`).

```text
Dockerfile (LOG_CHANNEL only)  +  .env.docker / project .env  →  print-env.php
```

## File roles

| File | Committed? | Role |
| ---- | ---------- | ---- |
| [`.env.example`](.env.example) | Yes | Team contract (placeholders) |
| [`.env`](.env) | No | Compose `${DB_PASSWORD}` interpolation |
| [`.env.docker`](.env.docker) | No | Runtime keys for `app` via `env_file` |
| [`.dockerignore`](.dockerignore) | Yes | Blocks `.env` from build context |

## Quick demo (solution)

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Creates gitignored `.env` / `.env.docker`, builds once, verifies runtime env, inspects image/history, changes env without rebuild, tests `${DB_PASSWORD:?}` fail-fast.

## Try it manually

```bash
cp .env.example .env   # edit DB_PASSWORD locally
cat > .env.docker <<'EOF'
APP_ENV=local
APP_DEBUG=true
DB_HOST=db
DB_DATABASE=academy
APP_KEY=not-a-real-app-key
EOF

docker compose build
docker compose run --rm app php print-env.php

docker image inspect ch8-exercise-env:demo --format '{{ json .Config.Env }}'
docker history --no-trunc ch8-exercise-env:demo | head -15

# edit .env.docker APP_ENV=staging — re-run without build
docker compose run --rm app php print-env.php

mv .env .env.bak && docker compose config 2>&1 | head -5; mv .env.bak .env
docker compose down -v
```

## Checklist

- [ ] `.env.example` committed; `.env` / `.env.docker` gitignored
- [ ] No `APP_KEY` / `DB_PASSWORD` in `Dockerfile`
- [ ] `env_file: [.env.docker]` on `app`
- [ ] `${DB_PASSWORD:?set DB_PASSWORD in .env}` on `db`
- [ ] `docker history` shows no baked secrets
- [ ] `.env.docker` edit changes output without rebuild

## Related

| Lesson | Folder |
| ------ | ------ |
| Dotenv Laravel-style | [dotenv-laravel-style](../dotenv-laravel-style/) |
| Build vs runtime | [build-vs-runtime-demo](../build-vs-runtime-demo/) |

← [Chapter 8 — Env and secrets](../README.md)
