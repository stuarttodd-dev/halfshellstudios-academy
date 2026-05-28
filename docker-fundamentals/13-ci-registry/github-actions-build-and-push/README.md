# GitHub Actions: build and push

**Course page:** [GitHub Actions build and push](https://docker.learnio.dev/learn/sections/chapter-ci-registry/ci-registry-github-actions-build-and-push)

Checkout → Buildx → login → `build-push-action` with `push: true` → capture digest. Tags are for humans; digests are what environments promote (lesson 13.2).

```text
git push → workflow → ghcr.io/OWNER/REPO:sha + :main → steps.build.outputs.digest
```

## Workflow (GHCR)

[`.github/workflows/image.yml`](.github/workflows/image.yml) is the lesson workflow:

| Step | Action |
| ---- | ------ |
| Checkout | `actions/checkout@v4` |
| BuildKit | `docker/setup-buildx-action@v3` |
| Login | `docker/login-action@v3` + `GITHUB_TOKEN` |
| Build & push | `docker/build-push-action@v6` with `push: true` |
| Digest | `steps.build.outputs.digest` |

**Using it in your app repo:** copy `image.yml` to `.github/workflows/` at the **repository root**, alongside this `Dockerfile`. Set `permissions.packages: write`. Image names must be lowercase — use `${{ github.repository }}`.

Fork PRs cannot push to GHCR without extra setup; use `push` to `main` or `workflow_dispatch` while learning.

## Quick demo (local, no GitHub)

Rehearses `build-push-action` against a throwaway registry (lesson try-it §1):

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

## Try it on GitHub

1. Fork or create a test repo with this `Dockerfile` and workflow at repo root.
2. Push to `main` (or run **workflow_dispatch** in Actions).
3. **Packages** → container image → tags `:<sha>` and `:main`.
4. Compare workflow log `digest=` with:

```bash
docker buildx imagetools inspect ghcr.io/OWNER/REPO:COMMIT_SHA \
  --format '{{json .Manifest.Digest}}'
```

Pull (needs `read:packages` PAT or `gh auth token`):

```bash
echo "$GITHUB_TOKEN" | docker login ghcr.io -u YOUR_USER --password-stdin
docker pull ghcr.io/OWNER/REPO:COMMIT_SHA
docker image inspect ghcr.io/OWNER/REPO:COMMIT_SHA --format '{{index .RepoDigests 0}}'
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | Minimal PHP image CI builds |
| [`image.yml`](.github/workflows/image.yml) | GHCR build-and-push workflow |
| [`demo.sh`](demo.sh) | Local registry + `buildx --push` + digest |
| [`clean.sh`](clean.sh) | Remove registry container |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `denied … push` | Add `permissions.packages: write` |
| `repository name must be lowercase` | Use `${{ github.repository }}` in tags |
| No digest in log | Set `push: true`; fix build errors first |
| `Dockerfile not found` | Match `file:` path to repo layout |
| Local push HTTPS errors | Add `127.0.0.1:5001` to Docker Desktop insecure registries |

## Related

| Lesson | Topic |
| ------ | ----- |
| 13.1 | From git push to digest |
| 13.4 | Pin prod to digest in Compose |

← [Chapter 13 — CI and registry](../README.md)
