# Publishing ports vs internal traffic

**Course page:** [Publishing ports vs internal traffic](https://docker.learnio.dev/learn/sections/chapter-networks/networks-publishing-ports-vs-internal-traffic)

| Mechanism | Who connects | This demo |
| --------- | ------------ | --------- |
| `ports:` | Host (browser, `curl` on laptop) | `edge` → `127.0.0.1:8080:80` |
| Service DNS + internal port | Other containers on the project network | `probe` → `cache:6379` |
| Neither | — | `cache` has no host publish |

```text
Host curl :8080  →  edge (published)
probe  →  cache:6379  (internal only)
probe  →  http://edge  (internal HTTP, no host port needed)
```

## Quick demo

```bash
chmod +x demo.sh down.sh
./demo.sh
./down.sh
```

If port 8080 is busy, `demo.sh` picks another port or set `HTTP_PORT` in `.env`.

## Try it manually (course steps)

```bash
docker compose -f compose.ports-demo.yaml up -d

curl -sS -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8080/
docker compose -f compose.ports-demo.yaml port edge 80

docker compose -f compose.ports-demo.yaml exec probe getent hosts cache
# busybox:1.36 has no getent — use: docker compose ... exec probe nslookup cache
docker compose -f compose.ports-demo.yaml exec probe nc -zv cache 6379

nc -zv 127.0.0.1 6379 2>&1 || true   # expect failure (unless something else on host uses 6379)

docker compose -f compose.ports-demo.yaml exec probe wget -qO- http://edge | head -3

docker compose -f compose.ports-demo.yaml down
```

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.ports-demo.yaml`](compose.ports-demo.yaml) | `edge` published; `probe` + `cache` internal |
| [`demo.sh`](demo.sh) | Automated checks from the lesson |
| [`down.sh`](down.sh) | `docker compose down` |
| [`.env.example`](.env.example) | `HTTP_PORT` override |

## PHP stack reminder (lesson table)

| Service | Publish to host? |
| ------- | ---------------- |
| nginx / `web` | Yes — browser entry |
| PHP-FPM / `app` | No — `expose: 9000`, nginx uses `app:9000` |
| MySQL / `db` | No — `DB_HOST=db` |
| Redis | No — `REDIS_HOST=redis` |

Full stack: [two-service-dns](../two-service-dns/) (`compose.php-mysql-redis.yaml`).

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `curl` to edge fails | `docker compose ps edge`; port conflict → `HTTP_PORT` in `.env` |
| App uses `127.0.0.1` for DB inside Compose | Use service name `db`, not `localhost` |
| `nc` to host `6379` succeeds unexpectedly | Another process on the laptop uses 6379; `demo.sh` also checks Compose publish map |
| `expose:` alone does not reach host | Add `ports:` only when the laptop must connect |

← [Chapter 6 — Networks](../README.md)
