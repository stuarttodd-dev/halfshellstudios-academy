# Connection refused: nothing listening

**Course page:** [Connection refused: nothing listening](https://docker.learnio.dev/learn/sections/chapter-networks/networks-connection-refused-nothing-listening)

`connection refused` means you reached the IP and port, but nothing accepted the connection yet — usually **timing** (DB still starting), **wrong port**, or **wrong host** (`127.0.0.1` instead of `db`).

```text
getent hosts db  →  OK?  →  nc -zv db 3306  →  refused? wait / healthcheck
```

## Quick demo

```bash
chmod +x demo.sh down.sh
./demo.sh
```

## Try it manually

```bash
docker compose -f compose.refused-demo.yaml up -d

docker compose -f compose.refused-demo.yaml exec probe nslookup db
# busybox has no getent — use nslookup or ping db
docker compose -f compose.refused-demo.yaml exec probe nc -zv db 3306

# When db logs show "ready for connections", nc again — expect open

docker compose -f compose.refused-demo.yaml down

# With healthcheck + service_healthy:
docker compose -f compose.refused-demo-healthy.yaml up -d
docker compose -f compose.refused-demo-healthy.yaml exec probe nc -zv db 3306
docker compose -f compose.refused-demo-healthy.yaml down
```

## Three-question habit

1. Did the name resolve? (`getent hosts db`)
2. Is the target process up? (`docker compose ps`, `logs db`)
3. Is it listening on the port you think? (`nc -zv db 3306` from a peer)

## Symptom → check → fix

| Symptom | Check | Fix |
| ------- | ----- | --- |
| `connection refused` on `db:3306` | `nc` from peer; `logs db` | Wait for MySQL; add `healthcheck` + `service_healthy` |
| `could not resolve host` | `getent hosts db` | Service name / same network (lesson 6.1) |
| Refused on `127.0.0.1` inside app | `printenv DB_HOST` | Use `DB_HOST=db`, not localhost |
| Refused on `app:9000` | FPM socket vs TCP | Align nginx + pool (lesson 11.4) |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.refused-demo.yaml`](compose.refused-demo.yaml) | `depends_on: service_started` only |
| [`compose.refused-demo-healthy.yaml`](compose.refused-demo-healthy.yaml) | `mysqladmin ping` healthcheck + `service_healthy` |
| [`demo.sh`](demo.sh) | DNS → early `nc` → wait → healthy variant |
| [`down.sh`](down.sh) | Tear down both stacks |

## Related

| Lesson | Folder |
| ------ | ------ |
| Container DNS | [container-dns-demo](../container-dns-demo/) |
| Publishing vs internal | [publish-vs-internal](../publish-vs-internal/) |
| PHP + MySQL by name | [two-service-dns](../two-service-dns/) |

← [Chapter 6 — Networks](../README.md)
