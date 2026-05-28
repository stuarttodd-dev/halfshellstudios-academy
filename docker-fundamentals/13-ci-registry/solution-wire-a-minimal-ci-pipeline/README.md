# Solution: wire a minimal CI pipeline

| Lesson | Course page |
| ------ | ----------- |
| **Solution 13.10** | [Solution: wire a minimal CI pipeline](https://docker.learnio.dev/learn/sections/chapter-ci-registry/ci-registry-solution-wire-a-minimal-ci-pipeline) |
| Exercise 13.9 | [Exercise: wire a minimal CI pipeline](https://docker.learnio.dev/learn/sections/chapter-ci-registry/ci-registry-exercise-wire-a-minimal-ci-pipeline) |

The solution lesson (`github_example: 13-ci-registry/solution-wire-a-minimal-ci-pipeline`) lives in this folder. The [exercise starter](../exercise-wire-a-minimal-ci-pipeline/) has the skeleton workflow you complete first.

Completed reference for chapter 13: one workflow that builds, smokes, scans, pushes to GHCR, and comments the digest on PRs.

```text
PR → build+load → smoke → Trivy → push GHCR → digest comment
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | PHP CLI image CI builds |
| [`smoke.php`](smoke.php) | Smoke output `ch13 ci exercise ok` |
| [`.github/workflows/image.yml`](.github/workflows/image.yml) | Full chapter 13 pipeline |
| [`compose.staging.yaml`](compose.staging.yaml) | Optional digest pin (13.4) |
| [`Dockerfile.broken`](Dockerfile.broken) | `CMD ["false"]` — smoke gate test |

## Compare to your exercise repo

If you started from the [exercise starter](../exercise-wire-a-minimal-ci-pipeline/), diff your completed `image.yml` against this folder:

```bash
diff -u ~/ch13-ci-exercise/.github/workflows/image.yml \
  solution-wire-a-minimal-ci-pipeline/.github/workflows/image.yml
```

Or copy this folder to run the reference pipeline end to end:

```bash
cp -R solution-wire-a-minimal-ci-pipeline ~/ch13-ci-solution
cd ~/ch13-ci-solution
git init && git add . && git commit -m "Add chapter 13 CI pipeline solution"
git remote add origin git@github.com:YOU/ch13-ci-solution.git
git push -u origin main
```

Open a PR from a branch to trigger the workflow. **Fork PRs may not push to GHCR** — use a branch in the same repo (lesson 13.6).

## Quick demo (solution 13.10)

```bash
chmod +x demo.sh solution-demo.sh clean.sh
./solution-demo.sh
```

Validates workflow YAML spine, runs build → smoke → optional Trivy → local registry push with digest (simulates GHCR). `./demo.sh` is the same script.

## Workflow highlights

| Step | Lesson |
| ---- | ------ |
| `load: true` candidate build | 13.5 |
| `docker run` smoke | 13.5 |
| `trivy-action` `exit-code: 1` | 13.7 |
| `id: build` push + SHA / `pr-N` tags | 13.3, 13.4 |
| `github-script` digest comment | 13.8 |

## Checklist

- [x] `permissions`: `packages: write`, `pull-requests: write`
- [x] Smoke and Trivy run **before** push
- [x] Push step has `id: build`
- [x] PR comment includes full `@sha256` digest
- [x] Broken `CMD` fails CI before GHCR publish

## Related

| Lesson | Folder |
| ------ | ------ |
| Exercise starter | [exercise-wire-a-minimal-ci-pipeline](../exercise-wire-a-minimal-ci-pipeline/) |
| GitHub Actions build and push | [github-actions-build-and-push](../github-actions-build-and-push/) |
| Smoke before push | [smoke-the-image-in-ci-before-push](../smoke-the-image-in-ci-before-push/) |
| Tag SHA / pin digest | [tag-with-git-sha-and-pin-prod-to-digest](../tag-with-git-sha-and-pin-prod-to-digest/) |

← [Chapter 13 — CI and registry](../README.md)
