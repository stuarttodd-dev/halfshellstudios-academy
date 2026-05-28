# Exercise: wire a minimal CI pipeline

| Lesson | Course page |
| ------ | ----------- |
| Exercise 13.9 | [Wire a minimal CI pipeline](https://docker.learnio.dev/learn/sections/chapter-ci-registry/ci-registry-exercise-wire-a-minimal-ci-pipeline) |
| Solution 13.10 | [Solution: wire a minimal CI pipeline](https://docker.learnio.dev/learn/sections/chapter-ci-registry/ci-registry-solution-wire-a-minimal-ci-pipeline) |

Chapter 13 checkpoint: complete one workflow that builds, smokes, scans, pushes to GHCR, and comments the digest on pull requests.

```text
PR → build+load → smoke → Trivy → push GHCR → digest comment
```

## Starter vs solution

| Folder | Role |
| ------ | ---- |
| [`exercise-wire-a-minimal-ci-pipeline`](../exercise-wire-a-minimal-ci-pipeline/) (this folder) | Starter skeleton — finish `.github/workflows/image.yml` tasks 1–6 |
| [`solution-wire-a-minimal-ci-pipeline`](../solution-wire-a-minimal-ci-pipeline/) | Completed reference workflow |

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | PHP CLI image CI builds |
| [`smoke.php`](smoke.php) | Smoke output `ch13 ci exercise ok` |
| [`.github/workflows/image.yml`](.github/workflows/image.yml) | Skeleton workflow (you complete it) |
| [`Dockerfile.broken`](Dockerfile.broken) | `CMD ["false"]` — smoke gate test after solution |
| [`compose.staging.yaml`](compose.staging.yaml) | Optional digest pin template (13.4) |

## Use in your GitHub repo

Copy this folder to a new repository root (or use as the repo):

```bash
cp -R exercise-wire-a-minimal-ci-pipeline ~/ch13-ci-exercise
cd ~/ch13-ci-exercise
git init && git add . && git commit -m "Add chapter 13 CI exercise starter"
git remote add origin git@github.com:YOU/ch13-ci-exercise.git
git push -u origin main
```

Complete the workflow tasks in the lesson, push, and open a PR. **Fork PRs may not push to GHCR** — use a branch in the same repo (lesson 13.6).

When stuck, compare against the [solution folder](../solution-wire-a-minimal-ci-pipeline/).

## Quick demo (local only)

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Builds the image and runs smoke locally. Full workflow spine checks live in the solution folder's `demo.sh`.

## Checklist

- [ ] `permissions`: `packages: write`, `pull-requests: write`
- [ ] Smoke and Trivy run **before** push
- [ ] Push step has `id: build`
- [ ] PR comment includes full `@sha256` digest
- [ ] Broken `CMD` fails CI before GHCR publish

## Related

| Lesson | Folder |
| ------ | ------ |
| GitHub Actions build and push | [github-actions-build-and-push](../github-actions-build-and-push/) |
| Smoke before push | [smoke-the-image-in-ci-before-push](../smoke-the-image-in-ci-before-push/) |
| Tag SHA / pin digest | [tag-with-git-sha-and-pin-prod-to-digest](../tag-with-git-sha-and-pin-prod-to-digest/) |
| Solution reference | [solution-wire-a-minimal-ci-pipeline](../solution-wire-a-minimal-ci-pipeline/) |

← [Chapter 13 — CI and registry](../README.md)
