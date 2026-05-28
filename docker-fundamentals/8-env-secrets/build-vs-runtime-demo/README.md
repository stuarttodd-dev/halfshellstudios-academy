# Build-time vs run-time configuration

**Course page:** [Build-time vs run-time configuration](https://docker.learnio.dev/learn/sections/chapter-env-secrets/env-secrets-build-time-vs-run-time-configuration)

Worked example for chapter 8 — see what belongs in `ARG` / image layers versus what you inject when a container starts.

## Three moments

| Moment | Question | Examples | Wrong habit |
| ------ | -------- | -------- | ----------- |
| Build time | Bake into the image? | `APP_VERSION`, compile flags | `DB_HOST` or API keys in the Dockerfile |
| Run time | Change at container start? | `DB_HOST=db`, `APP_ENV=local` | Rebuilding because staging uses a different DB host |
| Deploy time | Who owns production secrets? | Vault, CI secret store | Real passwords in git |

## What belongs where (PHP app)

| Value | Prefer | Why |
| ----- | ------ | --- |
| `APP_VERSION` / git SHA | build `ARG` or `LABEL` | Tied to the shipped artifact |
| `LOG_CHANNEL=stderr` | image `ENV` default | Safe default, not secret |
| `DB_HOST`, `REDIS_HOST` | run-time `env_file` / Compose | Per environment without rebuild |
| `APP_KEY`, DB password | run-time secret delivery | Must not land in layers (lessons 8.4+) |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | `ARG APP_VERSION` (build) + `ENV LOG_CHANNEL` (default) |
| [`.env.example`](.env.example) | Run-time keys only — team contract, not image bake |
| [`compose.example.yaml`](compose.example.yaml) | Illustrative `environment:` + `env_file:` |
| [`demo.sh`](demo.sh) | Lesson build/run/inspect sequence |
| [`clean.sh`](clean.sh) | Remove demo images |

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
./clean.sh
```

## Try it (manual)

From this folder:

```bash
docker build --build-arg APP_VERSION=1.0.0 -t ch8-config:v1 .
docker build --build-arg APP_VERSION=2.0.0 -t ch8-config:v2 .
docker run --rm ch8-config:v1 cat /built-version.txt
docker run --rm ch8-config:v2 cat /built-version.txt
```

`APP_VERSION` changed the image — that is **build-time** configuration.

Same image, different run-time env without rebuilding:

```bash
docker run --rm ch8-config:v1 sh -c 'echo LOG_CHANNEL=$LOG_CHANNEL'
docker run --rm -e LOG_CHANNEL=stdout -e DB_HOST=db ch8-config:v1 sh -c 'echo LOG_CHANNEL=$LOG_CHANNEL DB_HOST=$DB_HOST'
```

`DB_HOST` never touched the Dockerfile.

Peek at visibility:

```bash
docker image inspect ch8-config:v1 --format '{{ json .Config.Env }}'
docker history ch8-config:v1
```

You should see `LOG_CHANNEL` in image config and the `RUN echo built at version...` line in history. That is why secrets do not belong in those paths.

Clean up:

```bash
docker rmi ch8-config:v1 ch8-config:v2
```

## Troubleshooting

### Rebuilt the image to change `DB_HOST` or `APP_ENV`

Use run-time env instead — see [`compose.example.yaml`](compose.example.yaml) or:

```bash
docker run --rm -e DB_HOST=db -e APP_ENV=staging ch8-config:v1 sh -c 'echo $DB_HOST $APP_ENV'
```

### Password in Dockerfile `ENV` or plain `ARG`

Rotate the credential, remove it from the Dockerfile, use build secrets or runtime secret mounts (lessons 8.4+), then:

```bash
docker history --no-trunc myapp:latest | grep -iE 'password|secret|token' || true
```

### `ARG` empty inside `RUN`

Pass at build time:

```bash
docker build --build-arg APP_VERSION=1.2.3 -t ch8-config:v1 .
```

`ARG` does not persist in running containers unless copied to `ENV`.

### Compose `.env` not applied

Service `env_file:` is separate from Compose interpolation — see [`compose.example.yaml`](compose.example.yaml) and lesson 8.5 precedence.

## Lesson acceptance

- Two builds with different `--build-arg APP_VERSION` produce different `/built-version.txt` content
- Same `ch8-config:v1` image accepts `-e LOG_CHANNEL=stdout` and `-e DB_HOST=db` without rebuild
- You can explain build vs run vs deploy for a new config variable

## What you proved

- Stable images plus run-time config keeps PHP apps portable across environments
- Visibility in `docker inspect` and `docker history` shows why secrets need different paths

← [Chapter 8 — Env and secrets](../README.md)
