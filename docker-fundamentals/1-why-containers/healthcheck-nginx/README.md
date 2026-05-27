# Solution: add a healthcheck to nginx

**Course page:** [Solution: add a healthcheck](https://docker.learnio.dev/learn/sections/chapter-why-containers/why-containers-solution-add-a-healthcheck)

**Exercise:** [Exercise: add a healthcheck](https://docker.learnio.dev/learn/sections/chapter-why-containers/why-containers-exercise-add-a-healthcheck)

Worked solution for chapter 1 — a minimal nginx image with a `HEALTHCHECK` that probes HTTP inside the container.

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | `nginx:stable-alpine` plus a `HEALTHCHECK` using `wget` against `http://127.0.0.1/` |

The base image already includes nginx and `wget`. The healthcheck runs in the container’s network namespace, so `127.0.0.1` is the nginx process in the same container.

## Build and run

From this folder:

```bash
docker build -t hello-health:local .
docker images hello-health
```

Run the container in the background (publish port `8080` on the host loopback):

```bash
docker run --rm -d --name hello-health -p 127.0.0.1:8080:80 hello-health:local
```

If `docker run` fails, see [Troubleshooting](#troubleshooting) below.

## How to test

Work through these in order.

| Step | What to do | What you should see |
| ---- | ---------- | ------------------- |
| 1 | Health status right after start | `starting` (probes still running) |
| 2 | Health status after a few seconds | `healthy` |
| 3 | HTTP from the host | `HTTP/1.1 200 OK` (or similar) from `curl -I` |

**1 — Health status**

```bash
docker inspect --format '{{.State.Health.Status}}' hello-health
```

Wait a few seconds and run the same command again. On a healthy nginx container you should see `healthy` once the probes succeed.

To see recent probe results:

```bash
docker inspect --format '{{json .State.Health}}' hello-health
```

**2 — HTTP still works**

```bash
curl -I http://127.0.0.1:8080
```

You want a successful HTTP response in addition to a `healthy` health status. A container can be `Up` and `healthy` while still worth checking with a real request from outside.

**3 — Clean up**

```bash
docker stop hello-health
```

Because you used `--rm`, Docker removes the container after it stops.

## Troubleshooting

### Port `8080` already in use

**Symptom:** `docker run` prints a container ID, then fails with:

```text
failed to bind port 127.0.0.1:8080/tcp: listen tcp4 127.0.0.1:8080: bind: address already in use
```

Something else on your Mac is already listening on `127.0.0.1:8080` — often a **leftover `hello-health` container**, another dev server, or a previous lab still running.

**Option A — Reuse the port (find and stop what’s using it)**

See whether `hello-health` is already running:

```bash
docker ps -a --filter name=hello-health
```

If it is, stop and remove it, then run again:

```bash
docker rm -f hello-health
docker run --rm -d --name hello-health -p 127.0.0.1:8080:80 hello-health:local
```

If the name is free but the port is still busy, see what is bound to `8080` (macOS):

```bash
lsof -nP -iTCP:8080 -sTCP:LISTEN
```

Stop that process or container, then retry `docker run`.

**Option B — Use a different host port**

Pick another port (for example `8081`) in **both** `docker run` and `curl`:

```bash
docker run --rm -d --name hello-health -p 127.0.0.1:8081:80 hello-health:local
curl -I http://127.0.0.1:8081
```

The healthcheck inside the container is unchanged — it still probes `http://127.0.0.1/` on port 80 inside the container.

### Container name `hello-health` already in use

**Symptom:**

```text
Conflict. The container name "/hello-health" is already in use
```

A stopped or running container still has that name.

```bash
docker rm -f hello-health
docker run --rm -d --name hello-health -p 127.0.0.1:8080:80 hello-health:local
```

(Use the same host port you chose if you switched to `8081` above.)

### `hello-health` already running from a previous attempt

**Symptom:** `docker run` fails, or you are not sure whether the lab is already up.

```bash
docker ps --filter name=hello-health
```

If you see `hello-health` with status `Up`, you can inspect health and hit HTTP without starting a second container:

```bash
docker inspect --format '{{.State.Health.Status}}' hello-health
curl -I http://127.0.0.1:8080
```

To start fresh:

```bash
docker rm -f hello-health
docker run --rm -d --name hello-health -p 127.0.0.1:8080:80 hello-health:local
```

### Health status stays `starting` for a long time

The Dockerfile uses `--interval=30s`, so the first successful probe may take up to ~30 seconds after start. Wait, then check again:

```bash
docker inspect --format '{{.State.Health.Status}}' hello-health
```

If it never becomes `healthy`:

```bash
docker logs hello-health
docker inspect --format '{{json .State.Health}}' hello-health
```

Confirm nginx is running and that `wget` is available in the image (`nginx:stable-alpine` includes it). Rebuild if you changed the `Dockerfile`:

```bash
docker build -t hello-health:local .
```

### `docker inspect` or `curl` fails: no such container / connection refused

| Problem | Likely cause | Fix |
| ------- | ------------ | --- |
| `Error: No such object: hello-health` | Container was removed or never started | Run `docker ps -a` and start with `docker run` again |
| `curl: (7) Failed to connect` | Container not running, or wrong host port | `docker ps` and match `curl` to the port in `PORTS` (e.g. `8081` if you changed it) |
| `Unable to find image 'hello-health:local'` | Image not built | `docker build -t hello-health:local .` from this folder |

## Lesson acceptance

- `HEALTHCHECK` probes real HTTP behaviour (not a no-op like `true`)
- `docker inspect` reports a health status for `hello-health`
- `curl -I` against the published port succeeds while the container is up

## What you proved

- A `HEALTHCHECK` can test real service behaviour
- Health status is separate from `docker ps` showing `Up`
- `docker inspect` is the right tool to read health state

← [Docker fundamentals](../../README.md)
