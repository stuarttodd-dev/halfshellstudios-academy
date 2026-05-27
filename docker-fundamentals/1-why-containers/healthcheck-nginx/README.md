# Solution: add a healthcheck to nginx

**Course page:** [Solution: add a healthcheck](http://127.0.0.1:38080/learn/sections/chapter-why-containers/why-containers-solution-add-a-healthcheck)

**Exercise:** [Exercise: add a healthcheck](http://127.0.0.1:38080/learn/sections/chapter-why-containers/why-containers-exercise-add-a-healthcheck)

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

If the name is already in use from a previous attempt:

```bash
docker rm -f hello-health
docker run --rm -d --name hello-health -p 127.0.0.1:8080:80 hello-health:local
```

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

## Lesson acceptance

- `HEALTHCHECK` probes real HTTP behaviour (not a no-op like `true`)
- `docker inspect` reports a health status for `hello-health`
- `curl -I` against the published port succeeds while the container is up

## What you proved

- A `HEALTHCHECK` can test real service behaviour
- Health status is separate from `docker ps` showing `Up`
- `docker inspect` is the right tool to read health state

← [Docker fundamentals](../../README.md)
