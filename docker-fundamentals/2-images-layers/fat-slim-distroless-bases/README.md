# Fat bases, slim bases, and distroless choices

**Course page:** [Fat bases, slim bases, and distroless choices](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-fat-slim-bases-and-distroless-choices)

Worked example for chapter 2 — compare Debian and Alpine pull sizes with the same `curl` app, then build a multi-stage Go binary into a distroless runtime.

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile.debian`](Dockerfile.debian) | `debian:bookworm-slim` + `apt` install `curl` (glibc, familiar tooling) |
| [`Dockerfile.alpine`](Dockerfile.alpine) | `alpine:3.20` + `apk` install `curl` (musl, often smaller pull) |
| [`Dockerfile.distroless`](Dockerfile.distroless) | Multi-stage: compile in `golang:bookworm`, run from `distroless/static-debian12` |
| [`main.go`](main.go) | Tiny Go program for the distroless example |

## Compare Debian vs Alpine

Same app shape — only the `FROM` line and package manager differ.

From this folder:

```bash
docker build -f Dockerfile.debian -t base-compare:debian .
docker build -f Dockerfile.alpine -t base-compare:alpine .
docker image ls base-compare
docker run --rm base-compare:debian
docker run --rm base-compare:alpine
```

| Step | What you should see |
| ---- | ------------------- |
| Both builds | Success; note `SIZE` in `docker image ls base-compare` |
| Both runs | A `curl` version line on stdout |

Alpine is often smaller on disk. That does not automatically make it your production default:

- glibc vs musl matters for some native PHP extensions and prebuilt binaries
- slim Debian images still include `apt` for quick packages during an incident
- a smaller base does not fix a bloated `COPY . .` or fat app layers above it

## Multi-stage distroless

```bash
docker build -f Dockerfile.distroless -t base-compare:distroless .
docker image ls base-compare
docker run --rm base-compare:distroless
```

You should see `hello from distroless`.

The final image has no shell, `apt`, or `apk`. Build tools stay in the `build` stage; only `/hello` lands in the runtime image. That pattern suits self-contained compiled apps; PHP-FPM often still wants build tooling and sometimes a shell unless you have another debug path.

## Read the numbers without overfitting

After all three builds:

```bash
docker image ls base-compare
```

| Tag | Typical role |
| --- | ------------ |
| `base-compare:debian` | Sane default when you want glibc and familiar debugging |
| `base-compare:alpine` | Smaller when your stack is validated on musl |
| `base-compare:distroless` | Tiny runtime when you accept no interactive shell |

## Try it yourself

1. Build `base-compare:debian` and `base-compare:alpine`
2. Record both `SIZE` values from `docker image ls base-compare`
3. Run both and confirm `curl --version` prints
4. Build and run `base-compare:distroless`
5. Pick which base you would use for a new internal API and cite size, libc, or debugging in one sentence

## Decision checklist

Before changing production `FROM` lines:

- Did you measure pull time and `docker image ls` size, or only read the tag name?
- Do native libraries or extensions assume glibc?
- Do you need a package manager or shell at runtime?
- If the final image has no shell, what is the on-call debug story?
- Can build-time tooling move to a separate stage instead of shrinking runtime below what incidents need?

## Lesson acceptance

- Debian and Alpine images both build and print `curl --version`
- You can compare `SIZE` between `base-compare:debian` and `base-compare:alpine`
- Distroless multi-stage build runs and prints `hello from distroless`
- You can name one reason Alpine might still be wrong for your stack

## What you proved

- The `FROM` line sets the floor for every layer above it
- Same app on two bases shows pull-size trade-offs with evidence
- Multi-stage builds separate **build fat** from **runtime slim**

← [Chapter 2 — Images and layers](../README.md)
