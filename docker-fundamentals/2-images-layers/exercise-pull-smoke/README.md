# Solution: pull and smoke-test an image

**Course page:** [Solution: pull and smoke-test an image](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-solution-pull-and-smoke-test-an-image)

**Exercise:** [Exercise: pull and smoke-test an image](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-exercise-pull-and-smoke-test-an-image)

Worked solution for chapter 2 — pull a public image, run it with a published port, smoke-test HTTP, and inspect tags and digests locally.

No `Dockerfile` here: you use the image as published (`nginx:stable-alpine`).

For the earlier walkthrough of the same loop, see [nginx-pull-run](../nginx-pull-run/).

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| This README | Pull → run → curl → digests → logs → clean up |

## Step 1 — Pull

```bash
docker pull nginx:stable-alpine
```

Docker downloads layer blobs from the registry and assembles a local image tagged `nginx:stable-alpine`.

## Step 2 — Run with a published port

```bash
docker run --rm -d --name smoke-nginx -p 127.0.0.1:8080:80 nginx:stable-alpine
```

| Flag | Meaning |
| ---- | ------- |
| `--rm` | Remove the container when it stops |
| `-d` | Detached (background) |
| `--name smoke-nginx` | Stable name for logs and `docker stop` |
| `-p 127.0.0.1:8080:80` | Host loopback 8080 → container port 80 |

If `docker run` fails, see [Troubleshooting](#troubleshooting) below.

If the name is already in use:

```bash
docker rm -f smoke-nginx
docker run --rm -d --name smoke-nginx -p 127.0.0.1:8080:80 nginx:stable-alpine
```

## Step 3 — Smoke-test HTTP

```bash
curl -I http://127.0.0.1:8080
```

You want a successful response line such as `HTTP/1.1 200 OK`.

## Step 4 — Inspect tags and digests

```bash
docker image ls --digests nginx
docker image inspect --format '{{.RepoTags}} {{.RepoDigests}}' nginx:stable-alpine
```

| Field | Meaning |
| ----- | ------- |
| `RepoTags` | Human-friendly names (for example `nginx:stable-alpine`) |
| `RepoDigests` | Immutable manifest digests recorded when you pulled the image |

A tag is enough to run locally; a digest documents the exact content you pulled.

## Step 5 — Logs and cleanup

```bash
docker logs --tail 20 smoke-nginx
docker stop smoke-nginx
```

With `--rm`, Docker removes the container after it stops. The image remains on disk for the next run.

## Troubleshooting

### Port `8080` already in use

**Symptom:**

```text
failed to bind port 127.0.0.1:8080/tcp: listen tcp4 127.0.0.1:8080: bind: address already in use
```

**Option A — Stop the existing container**

```bash
docker ps -a --filter name=smoke-nginx
docker rm -f smoke-nginx
docker run --rm -d --name smoke-nginx -p 127.0.0.1:8080:80 nginx:stable-alpine
```

**Option B — Use another host port**

```bash
docker run --rm -d --name smoke-nginx -p 127.0.0.1:8081:80 nginx:stable-alpine
curl -I http://127.0.0.1:8081
```

### `curl` fails while the container is up

```bash
docker ps --filter name=smoke-nginx
docker logs smoke-nginx
```

Match `curl` to the host port shown in `PORTS`.

### No repo digest in `docker image ls --digests`

Pull again, then re-run inspect:

```bash
docker pull nginx:stable-alpine
docker image inspect --format '{{.RepoTags}} {{.RepoDigests}}' nginx:stable-alpine
```

## Lesson acceptance

- `docker pull nginx:stable-alpine` succeeds
- `curl -I` against the published port returns a successful HTTP status
- `docker image ls --digests` shows digest information for `nginx`
- You can explain the difference between the tag you used and the digest Docker recorded

## What you proved

- A registry pull delivered a runnable image
- A tag is enough to run locally; digests document exact content
- A short smoke test catches many bad pulls before they reach a larger stack

← [Chapter 2 — Images and layers](../README.md)
