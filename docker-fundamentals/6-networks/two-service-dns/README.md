# Networks: DNS by service name

Chapter 6 examples in [`two-service-dns`](.) (per course manifest).

| Lesson | Entry point |
| ------ | ----------- |
| [Two containers on one user-defined network](https://docker.learnio.dev/learn/sections/chapter-networks/networks-two-containers-on-one-user-defined-network) | [`up.sh`](up.sh) + [`demo.sh`](demo.sh) |
| [PHP, MySQL, and Redis by service name](https://docker.learnio.dev/learn/sections/chapter-networks/networks-php-mysql-redis-by-service-name) | [`compose.php-mysql-redis.yaml`](compose.php-mysql-redis.yaml) |
| **Exercise / Solution: two services talk over DNS** | [`compose.two-services.yaml`](compose.two-services.yaml) + [`solution-demo.sh`](solution-demo.sh) |

---

## Exercise 6.9 / Solution 6.10: two services talk over DNS

**Exercise:** [two services talk over DNS](https://docker.learnio.dev/learn/sections/chapter-networks/networks-exercise-two-services-talk-over-dns)

**Solution:** [Solution: two services talk over DNS](https://docker.learnio.dev/learn/sections/chapter-networks/networks-solution-two-services-talk-over-dns)

Two containers on the **same** user-defined network reach each other by **service name** — no published port, no hard-coded IP, no `/etc/hosts` edits.

```text
client  --http://backend-->  backend (nginx)
         (Compose DNS)
```

### Quick demo (solution)

```bash
chmod +x solution-demo.sh up.sh demo.sh down.sh
./solution-demo.sh
```

Compose-only check:

```bash
docker compose -f compose.two-services.yaml run --rm client
```

### Acceptance checklist

- [ ] Both services on one Compose project network
- [ ] `client` resolves `backend` (`getent hosts backend` or `wget http://backend`)
- [ ] `backend` has no host port mapping (traffic is internal)
- [ ] No `127.0.0.1` between containers for cross-service calls

### Compare your exercise

| Your file | Solution reference |
| --------- | ------------------ |
| User-defined network + two containers | [`up.sh`](up.sh) / [`demo.sh`](demo.sh) |
| Compose `services:` with two names | [`compose.two-services.yaml`](compose.two-services.yaml) |
| Three-tier app stack | [`compose.php-mysql-redis.yaml`](compose.php-mysql-redis.yaml) |

Optional full stack: `RUN_PHP_STACK=1 ./solution-demo.sh`

---

## PHP, MySQL, and Redis by service name

**Course page:** [PHP, MySQL, and Redis by service name](https://docker.learnio.dev/learn/sections/chapter-networks/networks-php-mysql-redis-by-service-name)

Compose stack where the app uses **`DB_HOST=db`** and **`REDIS_HOST=redis`** — no container IPs, no MySQL/Redis ports on the host.

### Networking contract

| Service | Hostname in app | Published to laptop? |
| ------- | --------------- | -------------------- |
| `app` | — | `8000` (HTTP only) |
| `db` | `db` | No — internal `3306` |
| `redis` | `redis` | No — internal `6379` |

Wrong inside the app container: `DB_HOST=127.0.0.1` or `REDIS_HOST=localhost` (points at the app itself).

### Stack files

| Item | Purpose |
| ---- | ------- |
| [`compose.php-mysql-redis.yaml`](compose.php-mysql-redis.yaml) | `app`, `db`, `redis` on one project network |
| [`public/index.php`](public/index.php) | Resolve names + TCP reachability to `db:3306`, `redis:6379` |
| [`.env.stack.example`](.env.stack.example) | Env defaults |
| [`up-stack.sh`](up-stack.sh) / [`down-stack.sh`](down-stack.sh) / [`demo-stack.sh`](demo-stack.sh) | Start, stop, verify |

### Quick demo

```bash
chmod +x up-stack.sh down-stack.sh demo-stack.sh
./up-stack.sh
./demo-stack.sh
./down-stack.sh
```

### Verify (lesson commands)

```bash
docker compose -f compose.php-mysql-redis.yaml up -d
docker compose -f compose.php-mysql-redis.yaml exec app getent hosts db
docker compose -f compose.php-mysql-redis.yaml exec app getent hosts redis
curl http://127.0.0.1:8000/
```

---

## Two containers on one user-defined network

**Course page:** [Two containers on one user-defined network](https://docker.learnio.dev/learn/sections/chapter-networks/networks-two-containers-on-one-user-defined-network)

Two containers on user-defined bridge `demo-net` — alpine reaches **`http://web`** by name with no host port.

| Item | Purpose |
| ---- | ------- |
| [`up.sh`](up.sh) | Create `demo-net` and start `web` (nginx) |
| [`demo.sh`](demo.sh) | `wget http://web` from alpine |
| [`down.sh`](down.sh) | Remove `web` and `demo-net` |

```bash
chmod +x up.sh demo.sh down.sh
./up.sh && ./demo.sh && ./down.sh
```

## Related

| Lesson | Folder |
| ------ | ------ |
| DNS pitfalls (off-network) | [container-dns-demo](../container-dns-demo/) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `getent` fails for `db` | Services not on same Compose network; check `docker compose ps` |
| `db:3306 unreachable` from app | MySQL still starting; wait for healthy `db` |
| `127.0.0.1` in `.env` | Use service names `db` and `redis` for in-container config |
| Port 8000 in use | Set `APP_PORT` in `.env` from `.env.stack.example` |

← [Chapter 6 — Networks](../README.md)
