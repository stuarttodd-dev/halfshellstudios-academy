# Tags vs digests (sticky note vs serial number)

**Course page:** [Tags vs digests](https://docker.learnio.dev/learn/sections/chapter-images-layers/images-layers-tags-vs-digests)

Worked example for chapter 2 — read a tag and a digest from a pulled image, pin a base image in a `Dockerfile`, and see what to record from CI for a deploy.

## The simple rule

| Reference | What it is | Good for |
| --------- | ---------- | -------- |
| `app:1.4.2` | **Tag** — a nickname | README, chat, Compose files |
| `app@sha256:abc123…` | **Digest** — a fingerprint | “This exact build went to prod” |

A registry can move the tag `1.4.2` from image **A** to image **B**. The digest for **A** does not change when the tag moves.

See [`release-record.example.txt`](release-record.example.txt) for what to save after CI pushes your app.

## What’s in this folder

| File | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | App on `alpine:3.20` **pinned by digest** |
| [`Dockerfile.unpinned`](Dockerfile.unpinned) | Same app; base tag only (can drift) |
| [`release-record.example.txt`](release-record.example.txt) | Tag + digest fields for a deploy record |

## Get the base image digest

```bash
docker pull alpine:3.20
docker image inspect --format '{{index .RepoDigests 0}}' alpine:3.20
```

You should see one line like `alpine@sha256:…`. That hash is what [`Dockerfile`](Dockerfile) pins after `@`.

Registry lookup without a local pull (optional):

```bash
docker buildx imagetools inspect alpine:3.20 --format '{{json .Manifest}}' | head -c 400
```

Use a digest from a source you trust. When Alpine moves in the registry, refresh the pin in `Dockerfile` on purpose after testing.

## Build with a pinned base

From this folder:

```bash
docker build -t tags-demo:pinned .
docker run --rm tags-demo:pinned
```

You should see `built on pinned base`.

The `FROM` line in [`Dockerfile`](Dockerfile) looks like:

```dockerfile
FROM alpine:3.20@sha256:d9e853e87e55526f6b2917df91a2115c36dd7c696a35be12163d44e6e2a4b6bc
```

Tag for humans (`3.20`), digest for proof (`sha256:…`).

Compare with the unpinned variant:

```bash
docker build -f Dockerfile.unpinned -t tags-demo:unpinned .
```

Both may build today. The difference shows up when the registry retags `alpine:3.20` — unpinned builds can pick up new base layers without you changing the Dockerfile.

## Check what is running

After you run a container:

```bash
docker run --rm -d --name tags-demo tags-demo:pinned sleep 300
docker inspect --format '{{.Image}}' tags-demo
docker image inspect --format '{{.RepoDigests}}' tags-demo:pinned
docker rm -f tags-demo
```

Compare the image ID / repo digests to what CI saved. No match means you are not running the build you think you are.

## Try it yourself

1. `docker pull alpine:3.20`
2. `docker image inspect --format '{{index .RepoDigests 0}}' alpine:3.20` — copy the `sha256` line
3. Confirm [`Dockerfile`](Dockerfile) uses that digest (or update the pin if your pull differs by platform)
4. `docker build -t tags-demo:pinned .` and `docker run --rm tags-demo:pinned`
5. In one sentence: why is `app:1.4.2` alone weak for production?
6. In one sentence: what would you save from CI after a good deploy? (see [`release-record.example.txt`](release-record.example.txt))

**Sample answers**

- Tag only: the registry can reuse `1.4.2` on different image bytes, so two deploys with the same tag may not match.
- After CI: save the full digest (e.g. `ghcr.io/your-org/app@sha256:…`) for staging and production; keep the tag for humans if you like.

## Lesson acceptance

- You can read `RepoDigests` from `docker image inspect`
- [`Dockerfile`](Dockerfile) pins `FROM` with `@sha256:…`
- You can explain tag (nickname) vs digest (fingerprint) without jargon
- You know to record the digest from CI, not only the semver tag

## What you proved

- Tags are movable labels; digests identify exact content
- Pinning `FROM` stops silent base-image drift between builds
- Deploy records should include the digest that staging and production pull

← [Chapter 2 — Images and layers](../README.md)
