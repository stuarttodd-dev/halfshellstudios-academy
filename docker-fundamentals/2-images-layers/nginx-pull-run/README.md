# Pull, run, and smoke-test an image

**Course page:** [Pull, run, and smoke-test an image](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-pull-run-and-smoke-test-an-image)

Worked example for chapter 2 — pull a public image from a registry, run a container, and prove HTTP works with `curl` before you touch application code.

No `Dockerfile` here: you use the image as published (`nginx:stable-alpine`).

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| This README | Commands for pull → run → smoke test → clean up |

## Pull, run, and smoke test

Work through these in order.

| Step | Command | What you should see |
| ---- | ------- | ------------------- |
| 1 | `docker pull nginx:stable-alpine` | Layers download (or `Image is up to date`) |
| 2 | `docker run …` (below) | A container ID |
| 3 | `curl -I http://127.0.0.1:8080` | `HTTP/1.1 200 OK` (or similar) |
| 4 | `docker image ls nginx` | `nginx` with tag `stable-alpine` |
| 5 | `docker stop ch2-smoke` | Container stops; `--rm` removes it |

**1 — Pull the image**

```bash
docker pull nginx:stable-alpine
```

`nginx:stable-alpine` is **repository:tag**. Docker resolves it on Docker Hub (by default) and stores the image locally.

**2 — Run a container**

```bash
docker run --rm -d --name ch2-smoke -p 127.0.0.1:8080:80 nginx:stable-alpine
```

| Flag | Meaning |
| ---- | ------- |
| `--rm` | Remove the container when it stops |
| `-d` | Detached (background) |
| `--name ch2-smoke` | Stable name for logs and `docker stop` |
| `-p 127.0.0.1:8080:80` | Host port 8080 → container port 80 |
| `nginx:stable-alpine` | Image to run (must be pulled or present locally) |

If `docker run` fails, see [Troubleshooting](#troubleshooting) below.

**3 — Smoke test from the host**

```bash
curl -I http://127.0.0.1:8080
```

A quick `HEAD` request proves nginx is listening on the published port. Pull → run → curl is the default loop before blaming app code.

**4 — Confirm the image is local**

```bash
docker image ls nginx
```

You should see `nginx` with tag `stable-alpine` (and possibly other tags if you pulled them earlier).

**5 — Clean up**

```bash
docker stop ch2-smoke
```

With `--rm`, Docker removes the container after it stops. The image stays on disk for the next run.

## Troubleshooting

### `docker pull` hangs or fails

**Likely causes:** network issues, registry login required, or rate limiting on Docker Hub.

- Check connectivity and retry `docker pull nginx:stable-alpine`
- If you use a mirror or private registry, confirm your Docker daemon config

### Port `8080` already in use

**Symptom:**

```text
failed to bind port 127.0.0.1:8080/tcp: listen tcp4 127.0.0.1:8080: bind: address already in use
```

**Option A — Stop the existing container**

```bash
docker ps -a --filter name=ch2-smoke
docker rm -f ch2-smoke
docker run --rm -d --name ch2-smoke -p 127.0.0.1:8080:80 nginx:stable-alpine
```

**Option B — Use another host port**

```bash
docker run --rm -d --name ch2-smoke -p 127.0.0.1:8081:80 nginx:stable-alpine
curl -I http://127.0.0.1:8081
```

### `curl` fails while the container is up

```bash
docker ps --filter name=ch2-smoke
docker logs ch2-smoke
```

- Confirm `PORTS` shows `127.0.0.1:8080->80/tcp` (or the port you chose)
- Match `curl` to that host port

### Container name already in use

```bash
docker rm -f ch2-smoke
docker run --rm -d --name ch2-smoke -p 127.0.0.1:8080:80 nginx:stable-alpine
```

## Lesson acceptance

- `docker pull` succeeds for `nginx:stable-alpine`
- `docker run` starts a detached container named `ch2-smoke`
- `curl -I` against the published port returns a successful HTTP status
- `docker image ls nginx` shows the pulled image locally

## What you proved

- **Pull** brings an image into your local store
- **Run** starts a process from that image with a port mapping
- **Smoke test** (`curl`) checks behaviour from outside the container

← [Chapter 2 — Images and layers](../README.md)
