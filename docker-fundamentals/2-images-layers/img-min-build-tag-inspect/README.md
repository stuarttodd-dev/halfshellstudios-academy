# Build, tag, and inspect an image locally

**Course page:** [Build, tag, and inspect an image locally](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-build-tag-and-inspect-an-image-locally)

Worked example for chapter 2 — turn a tiny `Dockerfile` into a tagged image, run it once, and read what Docker stored (tags, metadata, layers).

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | Alpine base, one `RUN` layer, `CMD` prints `/hello.txt` |
| This README | Build, tag, inspect, and history commands |

## Build and run

From this folder:

```bash
docker build -t img-min:1 .
docker image ls img-min
docker run --rm img-min:1
```

| Step | What you should see |
| ---- | ------------------- |
| `docker build` | Layer steps; final line `naming to docker.io/library/img-min:1` |
| `docker image ls img-min` | Row for `img-min:1` with an `IMAGE ID` |
| `docker run` | `hello from image` on stdout |

`-t img-min:1` sets **repository:tag**. The trailing `.` is the **build context** (this directory).

## Add more tags (no rebuild)

Tags are labels on one image ID:

```bash
docker image tag img-min:1 img-min:latest
docker image tag img-min:1 img-min:backup
docker image ls img-min
```

All three tags should share the same `IMAGE ID`. You did not build twice.

## Inspect metadata and layers

```bash
docker image inspect --format 'tags={{.RepoTags}} arch={{.Architecture}}' img-min:1
docker image inspect --format 'layer count={{len .RootFS.Layers}}' img-min:1
docker history --no-trunc img-min:1
```

| Command | Shows |
| ------- | ----- |
| `docker image inspect` | Config, architecture, env, layer list |
| `docker history` | Which Dockerfile instruction created each layer (newest at top) |

In `docker history`, find the line from `RUN echo "hello from image" > /hello.txt` — that layer added `/hello.txt`.

## Registry-shaped name (local only)

Retagging for a registry hostname does not push anything yet:

```bash
docker image tag img-min:1 ghcr.io/your-org/img-min:1
docker image ls ghcr.io/your-org/img-min
```

Same image ID, extra name. Push comes later when you authenticate to the registry.

## Try it yourself

Work through the full loop in order:

1. `docker build -t img-min:1 .`
2. Confirm `docker image ls img-min` shows the tag; note `IMAGE ID`
3. `docker run --rm img-min:1` — confirm `hello from image`
4. `docker image tag img-min:1 img-min:latest` and `img-min:backup`; verify one ID, three names
5. `docker history --no-trunc img-min:1` — spot the `RUN echo` layer
6. Edit the message in the `Dockerfile`, rebuild as `img-min:2`, confirm `:1` and `:2` have different IDs

**Rebuild after editing the Dockerfile:**

```bash
# Change the echo line in Dockerfile, then:
docker build -t img-min:2 .
docker image ls img-min
```

`:1` and `:2` should show different `IMAGE ID` values.

## Troubleshooting

### `docker build` cannot find Dockerfile

Run commands from this directory, or pass the path explicitly:

```bash
docker build -t img-min:1 /path/to/img-min-build-tag-inspect
```

### `hello from image` does not print

```bash
docker run --rm img-min:1
docker history img-min:1
```

Rebuild if you changed the `Dockerfile` but did not rebuild:

```bash
docker build -t img-min:1 .
```

### Tags show different image IDs when they should match

You may have rebuilt between `docker image tag` calls. Tag again from the image you intend:

```bash
docker image tag img-min:1 img-min:latest
docker image ls img-min
```

## Lesson acceptance

- `docker build -t img-min:1 .` succeeds from this folder
- `docker run --rm img-min:1` prints `hello from image`
- Multiple tags (`:1`, `:latest`, `:backup`) can reference one `IMAGE ID`
- `docker image inspect` and `docker history` answer what is in the image and how it was built

## What you proved

- **Build** turns a `Dockerfile` + context into a local image
- **Tag** names an image without copying layers
- **Inspect** and **history** show metadata and layer provenance

← [Chapter 2 — Images and layers](../README.md)
