# ch15-split-exercise

Split dev and prod Compose overlays for a minimal nginx + PHP-FPM stack.

**Course exercise (15.9):** [Exercise: split dev and prod Compose](https://docker.learnio.dev/learn/sections/chapter-prod-patterns/prod-patterns-exercise-split-dev-and-prod-compose)

**Course solution (15.10):** [Solution: split dev and prod Compose](https://docker.learnio.dev/learn/sections/chapter-prod-patterns/prod-patterns-solution-split-dev-and-prod-compose)

Both lessons point at this folder (`github_example: 15-prod-patterns/exercise-split-dev-and-prod-compose`).

## Local development

Fast feedback with bind mount and Xdebug. Copy `.env.example` to `.env`. Files: `compose.yaml`, `compose.dev.yaml`, `.env`. Do not use production database credentials in `.env`.

```bash
make dev
# same as:
docker compose -f compose.yaml -f compose.dev.yaml --env-file .env up -d --build
```

## Production-shaped stack (local test)

Runs the `runtime` image without bind mount on app code. Uses `.env.prod.example` for variable shape.

```bash
docker build --target runtime -t ch15-split-exercise:release .
make prod-up
```

Validate merge before deploy:

```bash
make ci-check
make prod-config
```

## CI

Pull requests run `docker compose -f compose.yaml -f compose.prod.yaml --env-file .env.prod.example config -q`. See `.github/workflows/validate-compose.yml`.

## Quick verify

```bash
cp .env.example .env
./demo.sh
```

← [Chapter 15](../README.md) · [Docker fundamentals](../../README.md)
