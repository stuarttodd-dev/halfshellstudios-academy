# Tag with git SHA and pin prod to digest

**Course page:** [Tag with git SHA and pin prod to digest](https://docker.learnio.dev/learn/sections/chapter-ci-registry/ci-registry-tag-with-git-sha-and-pin-prod-to-digest)

One CI build, multiple tags for humans; production promotes the **digest** CI tested — not `:main` or `:latest`.

```text
push :sha + :main + :latest  →  one sha256 manifest  →  prod @sha256:…
```

## Tag jobs vs deploy trust

| Tag | Job | Safe for prod? |
| --- | --- | -------------- |
| Git SHA | Traceability to commit | Audit trail only |
| Branch (`:main`) | Newest on branch | No — pointer moves |
| `latest` | Default pull convenience | No |
| `@sha256:…` | Exact manifest bytes | Yes |

## Quick demo (local registry)

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Builds v1, pushes three tags with one digest, pulls by digest, pushes v2 to `:main` only, proves prod pin still runs v1, then `compose.prod.yaml` pull.

## Try it manually

```bash
docker run -d --name ch13-reg -p 127.0.0.1:5001:5000 registry:2
docker build -t 127.0.0.1:5001/ch13-app:sha-demo1234 .
docker tag 127.0.0.1:5001/ch13-app:sha-demo1234 127.0.0.1:5001/ch13-app:main
docker push 127.0.0.1:5001/ch13-app:sha-demo1234
docker push 127.0.0.1:5001/ch13-app:main

docker buildx imagetools inspect 127.0.0.1:5001/ch13-app:sha-demo1234 --format '{{json .Manifest.Digest}}'
docker pull 127.0.0.1:5001/ch13-app@sha256:YOUR_DIGEST
```

## GitHub Actions reference

[`.github/workflows/image-with-tags.yml`](.github/workflows/image-with-tags.yml) — push `:${{ github.sha }}`, `:main`, `:latest`; export `steps.build.outputs.digest` to a deploy job.

Copy to your app repo root. See also [github-actions-build-and-push](../github-actions-build-and-push/).

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | `build=v1` / demo rebuilds `v2` |
| [`compose.prod.yaml`](compose.prod.yaml) | Prod pin sketch (`@sha256:…`) |
| [`demo.sh`](demo.sh) | Local three-tag + digest promotion walkthrough |
| [`image-with-tags.yml`](.github/workflows/image-with-tags.yml) | CI reference workflow |

## Related

| Lesson | Folder |
| ------ | ------ |
| GitHub Actions build and push | [github-actions-build-and-push](../github-actions-build-and-push/) |
| Tags vs digests (vocabulary) | Course chapter 2 |

← [Chapter 13 — CI and registry](../README.md)
