# Container DNS without hard-coded IPs

**Course page:** [Container DNS without hard-coded IPs](https://docker.learnio.dev/learn/sections/chapter-networks/networks-container-dns-without-hard-coded-ips)

Worked example for chapter 6 — resolve a container by **name** on a user-defined network, and see why lookups fail when you are on the wrong network or use a stale IP in `.env`.

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`up.sh`](up.sh) | Create `ch6-dns-net` and start `ch6-web` |
| [`demo.sh`](demo.sh) | On-network and off-network DNS lookups |
| [`down.sh`](down.sh) | Remove container and network |
| [`compose.stub.yaml`](compose.stub.yaml) | Illustrative `DB_HOST=db` / `REDIS_HOST=redis` by service name |
| [`compose.env.example`](compose.env.example) | Env vars for the next lesson’s PHP stack |

## The rule

| Wrong (breaks when containers restart) | Right |
| -------------------------------------- | ----- |
| `DB_HOST=172.18.0.4` | `DB_HOST=db` (Compose service name) |
| `DB_HOST=127.0.0.1` inside the app container | `DB_HOST=db` on the project network |
| Editing `/etc/hosts` by hand | Docker embedded DNS on the shared network |

`localhost` inside the **app** container is the app itself — not MySQL or Redis beside it in Compose.

## Quick demo (scripts)

```bash
chmod +x up.sh demo.sh down.sh
./up.sh
./demo.sh
./down.sh
```

## Try it (manual commands)

### 1. Create a network and target container

```bash
docker network create ch6-dns-net
docker run -d --name ch6-web --network ch6-dns-net nginx:stable-alpine
```

### 2. Resolve from another container on the same network

```bash
docker run --rm --network ch6-dns-net alpine getent hosts ch6-web
docker run --rm --network ch6-dns-net busybox nslookup ch6-web
```

You should see an IP (often `172.x.x.x`) for `ch6-web` without hard-coding it anywhere.

### 3. Prove the name fails off the network

```bash
docker run --rm alpine getent hosts ch6-web
```

This usually fails: the lookup container is not on `ch6-dns-net`.

### 4. Clean up

```bash
docker rm -f ch6-web
docker network rm ch6-dns-net
```

## Debug DNS before blaming the app

```bash
docker compose exec app getent hosts db
```

Without Compose:

```bash
docker run --rm --network ch6-dns-net busybox nslookup db
```

If the name does not resolve, fix network membership or the service name before chasing PHP DSN strings.

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `getent hosts ch6-web` empty on-network | Confirm `ch6-web` is running and attached to `ch6-dns-net`; match the name exactly |
| Lookup fails | Add `--network ch6-dns-net` to the probe container |
| `DB_HOST=localhost` for `db` | Use service name `db`; verify with `docker compose exec app getent hosts db` |
| Hard-coded IP worked yesterday | Container was recreated — use service name, not IP in `.env` |
| `nslookup: not found` on Alpine | Use `getent hosts` on Alpine or `busybox nslookup` |
| Name resolves but `connection refused` | DNS OK — check port, healthcheck, and `depends_on` (lesson 6.6) |
| Wrong hostname (`mysql` vs `db`) | `DB_HOST` must match the **service key** in `compose.yaml` |

Inspect helpers:

```bash
docker ps --filter name=ch6-web
docker inspect ch6-web --format '{{ range $k, $v := .NetworkSettings.Networks }}{{ $k }} {{end}}'
docker network inspect ch6-dns-net --format '{{ json .Containers }}'
```

## Lesson acceptance

- `./demo.sh` shows `ch6-web` resolving on `ch6-dns-net`
- Off-network lookup does not return a useful result
- You can explain why `DB_HOST=db` beats `DB_HOST=172.18.0.x`

## What you proved

- Containers move; names on a user-defined network stay stable for DNS
- Resolution only works when both sides share the same network
- Configure apps with service names, then verify with `getent hosts` or `nslookup`

← [Chapter 6 — Networks](../README.md)
