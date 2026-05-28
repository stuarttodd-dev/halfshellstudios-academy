# Build a static site image (exercise + solution)

| Lesson | Use |
| ------ | --- |
| [Exercise: build a static site image](https://docker.learnio.dev/learn/sections/chapter-dockerfile-core/dockerfile-core-exercise-build-a-static-site-image) | Work in [`starter/`](starter/) — **no Dockerfile** there yet |
| [Solution: build a static site image](https://docker.learnio.dev/learn/sections/chapter-dockerfile-core/dockerfile-core-solution-build-a-static-site-image) | This folder’s [`Dockerfile`](Dockerfile) |
| [Minimal static site Dockerfile](https://docker.learnio.dev/learn/sections/chapter-dockerfile-core/dockerfile-core-minimal-static-site-dockerfile) | Same solution; lesson 3.3 intro |

## Exercise

From [`starter/`](starter/):

1. Add a `Dockerfile` meeting the exercise requirements (see course page).
2. Run the verification commands below.

Do not copy the parent `Dockerfile` until you are stuck or ready to compare.

## Solution

```dockerfile
FROM nginx:stable-alpine

WORKDIR /usr/share/nginx/html

# Remove stock welcome page so only our assets are served.
RUN rm -f ./*

COPY index.html ./
COPY styles.css ./

EXPOSE 80
```

| Requirement | How |
| ----------- | --- |
| `FROM nginx:stable-alpine` | Pinned base, not `latest` |
| `WORKDIR /usr/share/nginx/html` | nginx document root |
| Clear default files | `RUN rm -f ./*` before `COPY` |
| Separate `COPY` lines | One per asset (cache boundaries) |
| `EXPOSE 80` | Document listen port |
| hadolint clean | `hadolint --failure-threshold warning Dockerfile` |

## Quick verify (solution)

From **this directory** (parent of `starter/`):

```bash
chmod +x smoke.sh
./smoke.sh
```

Or manually:

```bash
hadolint --failure-threshold warning Dockerfile
docker build --progress=plain -t static-exercise:0.1 .
docker run --rm -d -p 127.0.0.1:8080:80 --name static-exercise static-exercise:0.1
curl -sS http://127.0.0.1:8080/ | grep "Built with my Dockerfile"
docker stop static-exercise
```

## Cache stretch goal

```bash
echo "/* cache bump */" >> styles.css
docker build --progress=plain -t static-exercise:0.2 .
```

Expect `FROM`, `WORKDIR`, and `RUN rm` **CACHED**; `COPY styles.css` reruns.

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`starter/`](starter/) | Exercise files only (you write `Dockerfile`) |
| [`Dockerfile`](Dockerfile) | Reference solution |
| [`index.html`](index.html) / [`styles.css`](styles.css) | Assets used by solution build |
| [`.dockerignore`](.dockerignore) | Keeps context small |
| [`.hadolint.yaml`](.hadolint.yaml) | `failure-threshold: warning` |
| [`smoke.sh`](smoke.sh) | Lint, build, run, curl, logs |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Still nginx welcome page | Confirm `WORKDIR` + `RUN rm -f ./*`; rebuild `--no-cache` once |
| `COPY failed` | Build from directory that contains the HTML/CSS |
| Port 8080 busy | Set `HTTP_PORT` in `.env` from [`.env.example`](.env.example) |
| hadolint not found | Install [hadolint](https://github.com/hadolint/hadolint); or skip and run build/curl |

## Lesson acceptance

- Dockerfile under ~20 lines, passes hadolint at warning threshold
- `curl` shows `Built with my Dockerfile`, HTTP `200`
- `docker image history` shows `COPY` after `RUN rm`

← [Chapter 3 — Dockerfile core](../README.md)
