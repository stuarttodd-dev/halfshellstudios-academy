# Build context and .dockerignore

**Course page:** [Build context and .dockerignore](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-build-context-and-dockerignore)

Worked example for chapter 2 — see what `docker build` sends as context, trim it with `.dockerignore`, and keep secrets out of layers.

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | Copies only `src/` into a tiny Alpine image |
| [`.dockerignore`](.dockerignore) | Excludes VCS metadata, deps, secrets, and local junk from the context |
| [`fixtures/`](fixtures/) | ~2 MB of tracked “junk” excluded by `.dockerignore` (reliable in git checkouts) |
| [`setup-demo.sh`](setup-demo.sh) | Optional extra local bloat (`node_modules/`, `vendor/`, `.env`) for `du` exploration |
| [`src/index.txt`](src/index.txt) | Small file the image actually needs |
| [`.env.example`](.env.example) | Safe template (re-included pattern in `.dockerignore`) |

Generated paths are listed in [`.gitignore`](.gitignore) so they are not committed.

## Prepare the demo context

The [`fixtures/`](fixtures/) directory is committed (~2 MB) so `docker build` shows a clear context-size drop with `.dockerignore`, even in a git checkout (BuildKit often skips untracked files).

Optional — extra local-only weight:

```bash
chmod +x setup-demo.sh
./setup-demo.sh
du -sh ./* ./.[!.]* 2>/dev/null | sort -h
```

You should see `fixtures/`, and after the script, `node_modules/` and `vendor/` as well.

## Build without `.dockerignore` (large context)

Temporarily disable the ignore file and build (legacy builder shows context size clearly):

```bash
mv .dockerignore .dockerignore.bak
DOCKER_BUILDKIT=0 docker build --no-cache -t demo:context .
mv .dockerignore.bak .dockerignore
```

Look for:

```text
Sending build context to Docker daemon  …MB
```

With [`fixtures/`](fixtures/) tracked in git, that should be on the order of megabytes.

**BuildKit (default):** `docker build --progress=plain` may report a small `transferring context` line in git checkouts because untracked files are often omitted. Use `DOCKER_BUILDKIT=0` above for the side-by-side size lesson, or rely on `du` plus the legacy builder line.

## Build with `.dockerignore` (small context)

```bash
DOCKER_BUILDKIT=0 docker build --no-cache -t demo:ignore .
docker run --rm demo:ignore
```

You should see `Sending build context to Docker daemon` around tens of kilobytes (not megabytes) and `hello from src/...` on stdout.

BuildKit users can also run:

```bash
docker build --no-cache --progress=plain -t demo:ignore .
```

Sanity-check ignored paths (when this folder is inside a git repo):

```bash
git check-ignore -v node_modules .env .git 2>/dev/null || true
```

## What belongs in the context

| Include | Exclude |
| ------- | ------- |
| Source under `src/` | `node_modules/`, `vendor/` (rebuilt in image or not needed) |
| Manifests you `COPY` | `.env`, `*.pem`, `*.key` |
| Assets referenced by `COPY` | `.git`, IDE dirs, `dist/`, `coverage/` |

The [`Dockerfile`](Dockerfile) only needs `src/`. Everything else is noise or risk if it rides along.

## Keep secrets out of layers

`.dockerignore` stops files from reaching the builder. For values needed during one build step, prefer secret mounts — not `COPY` or `ARG` that persist in history.

Runtime (secret when the container starts):

```bash
docker run --rm -e DATABASE_URL="$DATABASE_URL" myorg/api:latest
```

Build secret mount (only for that `RUN` line; requires BuildKit):

```dockerfile
# syntax=docker/dockerfile:1.7
RUN --mount=type=secret,id=registry_token,target=/run/secrets/token \
    npm ci --registry=https://registry.example.com/ \
      --//registry.example.com/:_authToken="$(cat /run/secrets/token)"
```

```bash
echo "$NPM_TOKEN" | docker build --secret id=registry_token,src=/dev/stdin -t myorg/api .
```

Private Git over SSH during build:

```dockerfile
RUN --mount=type=ssh \
    git clone git@github.com:myorg/private-lib.git /opt/private-lib
```

```bash
docker build --ssh default -t myorg/api .
```

## Try it yourself

1. Run [`setup-demo.sh`](setup-demo.sh) and list directory sizes with `du`
2. Build once with `.dockerignore` moved aside; note `transferring context` size
3. Build again with [`.dockerignore`](.dockerignore) in place; confirm the context shrank
4. Add one pattern to `.dockerignore` for something in your real project (logs, `tmp/`, local env files)

## Lesson acceptance

- You can explain what the build context path sends to Docker
- `transferring context` is much smaller with a sensible `.dockerignore`
- Secrets and dependency trees should not be copied into layers by mistake
- You know the difference between `.dockerignore` (never sent) and build secret mounts (one step only)

## What you proved

- Context hygiene makes builds faster and safer
- `.dockerignore` uses familiar gitignore-style patterns
- Smaller context pairs with a `Dockerfile` that only `COPY`s what it needs

← [Chapter 2 — Images and layers](../README.md)
