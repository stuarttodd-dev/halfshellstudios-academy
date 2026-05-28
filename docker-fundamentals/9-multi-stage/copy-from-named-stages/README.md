# COPY --from and named stages

**Course page:** [COPY --from and named stages](https://docker.learnio.dev/learn/sections/chapter-multi-stage/multi-stage-copy-from-and-named-stages)

Name stages with `AS`, copy explicit paths with `COPY --from`, and build production with `--target` on the runtime stage.

```text
FROM … AS build   →  toolchains, temp dirs (discarded)
FROM … AS runtime →  COPY --from=build /out/release.txt only
```

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

## Try it manually (course steps)

```bash
docker build -t ch9-named:runtime .
docker run --rm ch9-named:runtime

docker run --rm ch9-named:runtime sh -c 'command -v gcc || echo "no gcc in runtime"'

docker build --target build -t ch9-named:build-only .
docker image ls ch9-named:runtime ch9-named:build-only
docker run --rm ch9-named:build-only sh -c 'command -v gcc && test -f /out/release.txt'
```

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile`](Dockerfile) | `build` + `runtime` stages; whitelist one file |
| [`Dockerfile.numeric`](Dockerfile.numeric) | Fragile `COPY --from=0` anti-pattern (discussion) |
| [`Dockerfile.composer-sketch`](Dockerfile.composer-sketch) | PHP-shaped `deps` → `runtime` pattern (lesson 9.3 preview) |
| [`demo.sh`](demo.sh) | Build, run, `--target build`, size compare |
| [`clean.sh`](clean.sh) | Remove `ch9-named:*` images |

## Stage contract (runtime)

1. `COPY --from=build /out/release.txt` — approved artifact only  
2. No `gcc`, `build-base`, or `/` copy from build  
3. Production CI: `docker build --target runtime -t myapp:release .`

## Related

| Lesson | Notes |
| ------ | ----- |
| Build vs runtime (9.1) | Why multi-stage — course try-it with `Dockerfile.single` |
| Composer → PHP-FPM (9.3) | Full PHP multi-stage in course + `Dockerfile.composer-sketch` |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `stage build not found` | Typo in `AS` name or `COPY --from` |
| `COPY failed: file not found` | Build `--target build` and inspect paths in source stage |
| Runtime still huge | You copied `/` instead of explicit paths |
| CI ships wrong image | Add `--target runtime` to production builds |

← [Chapter 9 — Multi-stage](../README.md)
