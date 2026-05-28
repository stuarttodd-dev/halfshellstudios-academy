# Run the CLI inner loop (exercise + solution)

| Lesson | Use |
| ------ | --- |
| [Exercise: run the CLI inner loop](https://docker.learnio.dev/learn/sections/chapter-cli-workflow/cli-workflow-exercise-run-the-cli-inner-loop) | Work through the commands below before running `demo.sh` |
| [Solution: run the CLI inner loop](https://docker.learnio.dev/learn/sections/chapter-cli-workflow/cli-workflow-solution-run-the-cli-inner-loop) | [`demo.sh`](demo.sh) — same sequence with checks |

No Dockerfile — only `nginx:stable-alpine` and the chapter 4 lifecycle commands.

## The loop

```text
run → ps → curl → logs → inspect → stop → ps -a (exited) → rm → ps -a (gone)
```

## Exercise

Complete these steps yourself (course page has the checklist):

```bash
docker pull nginx:stable-alpine
docker rm -f cli-loop 2>/dev/null || true

docker run -d --name cli-loop -p 127.0.0.1:8080:80 nginx:stable-alpine
docker ps --filter name=cli-loop
curl -I http://127.0.0.1:8080
docker logs --tail 10 cli-loop
docker inspect --format '{{.State.Status}} restarts={{.RestartCount}}' cli-loop

docker stop cli-loop
docker ps -a --filter name=cli-loop
docker rm cli-loop
docker ps -a --filter name=cli-loop
```

## Solution

Automated walkthrough (matches the course solution lesson):

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

`demo.sh` uses host port **8080** by default. If it is busy, the script picks the next free port and prints which one it chose. Override with `cp .env.example .env` and set `HTTP_PORT`.

## Checklist

- [ ] `curl` returns a successful HTTP status (e.g. `200 OK`)
- [ ] `inspect` shows `running` before stop; `docker ps -a` shows `exited` after stop
- [ ] After `docker rm`, no container named `cli-loop`

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`demo.sh`](demo.sh) | Solution: full inner loop with assertions |
| [`clean.sh`](clean.sh) | `docker rm -f cli-loop` |
| [`.env.example`](.env.example) | Optional `HTTP_PORT` |

## Related

| Lesson | Folder |
| ------ | ------ |
| docker exec and one-offs | [exec-one-off-demo](../exec-one-off-demo/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Port 8080 in use | `export HTTP_PORT=8081`, or use `.env`, or let `demo.sh` pick a port |
| Name already in use | `./clean.sh` or `docker rm -f cli-loop` |
| `curl` connection refused | `docker ps` — container must be `Up` |

← [Chapter 4 — CLI workflow](../README.md)
