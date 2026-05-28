# Bind mounts, named volumes, and MySQL persistence

Chapter 5 examples in one folder (`bind-vs-named-php` per course manifest).

| Lesson | Section |
| ------ | ------- |
| [Mount a PHP project for local dev](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-mount-a-php-project-for-local-dev) | Bind-mount PHP below |
| [MySQL with a named volume](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-mysql-with-a-named-volume) | [MySQL named volume](#mysql-with-a-named-volume) |

---

## MySQL with a named volume

**Course page:** [MySQL with a named volume](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-mysql-with-a-named-volume)

Minimal Compose stack: MySQL 8 + `dbdata` named volume. Prove a row survives `docker compose down` (without `-v`).

### Files

| Item | Purpose |
| ---- | ------- |
| [`compose.mysql.yaml`](compose.mysql.yaml) | `db` service + top-level `volumes: dbdata` |
| [`.env.mysql.example`](.env.mysql.example) | `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE` |
| [`demo-mysql.sh`](demo-mysql.sh) | Up → seed → `down` → up → `SELECT` still shows row |
| [`demo-mysql-destroy.sh`](demo-mysql-destroy.sh) | `docker compose down -v` — wipes `dbdata` |
| [`mysql-down.sh`](mysql-down.sh) | `down` or `down -v` passthrough |

### Quick demo

```bash
chmod +x demo-mysql.sh demo-mysql-destroy.sh mysql-down.sh
./demo-mysql.sh
```

### Manual proof (lesson steps)

```bash
docker compose -f compose.mysql.yaml up -d

docker compose -f compose.mysql.yaml exec db mysql -uroot -psecret app -e \
  "CREATE TABLE notes (id INT AUTO_INCREMENT PRIMARY KEY, body TEXT);
   INSERT INTO notes (body) VALUES ('first row');"

docker compose -f compose.mysql.yaml down
docker compose -f compose.mysql.yaml up -d

docker compose -f compose.mysql.yaml exec db mysql -uroot -psecret app -e "SELECT * FROM notes;"
```

| Command | Containers | Named volume `dbdata` |
| ------- | ---------- | --------------------- |
| `docker compose down` | Removed | **Kept** |
| `docker compose down -v` | Removed | **Deleted** |

Clean up after experiments:

```bash
./mysql-down.sh -v
```

---

## Mount a PHP project for local dev

**Course page:** [Mount a PHP project for local dev](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-mount-a-php-project-for-local-dev)

Bind-mount your project directory into a PHP container so you edit on the host and run in the container runtime without rebuilding images.

## The title command

```bash
docker run --rm \
  -v "$PWD":/app \
  -w /app \
  php:8.3-cli php -v
```

| Flag | Meaning |
| ---- | ------- |
| `-v "$PWD":/app` | Bind-mount current directory → `/app` in the container |
| `-w /app` | Working directory for the command |
| `php:8.3-cli` | PHP runtime in the container |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`hello.php`](hello.php) | Tiny script run from the mount |
| [`bin/artisan-stub.php`](bin/artisan-stub.php) | `php artisan --version`-shaped one-off |
| [`demo.sh`](demo.sh) | Lesson bind-mount loop + `--mount` long form |
| [`demo-named.sh`](demo-named.sh) | Named volume persists across containers (lesson 5.2) |

## Quick demo

```bash
chmod +x demo.sh demo-named.sh
./demo.sh
./demo-named.sh
```

## Try it (manual)

From this folder:

```bash
docker run --rm \
  -v "$PWD":/app \
  -w /app \
  php:8.3-cli php hello.php
```

Edit `hello.php` on the host, run the same command again — output updates with no `docker build`.

Laravel-shaped paths:

```bash
docker run --rm \
  -v "$PWD":/var/www/html \
  -w /var/www/html \
  php:8.3-cli php bin/artisan-stub.php --version
```

Explicit mount syntax (preferred in scripts/reviews):

```bash
docker run --rm \
  --mount type=bind,source="$PWD",target=/app,readonly \
  -w /app \
  php:8.3-cli php hello.php
```

## Bind vs named (when to use which)

| Mount | Left side | Use for |
| ----- | --------- | ------- |
| Bind | Host path (`$PWD`, `./src`) | PHP/Laravel source you edit in the IDE |
| Named | Volume name (`dbdata`) | MySQL data, uploads — survives `docker rm` (lesson 5.4+) |

```bash
./demo-named.sh
```

Rule of thumb: **bind mounts for code you are changing locally**; **named volumes for data the app owns**; **image layers for code you ship**.

## Why not production?

Bind-mounting source is a **development** pattern. Staging and production should run immutable image artifacts (chapter 15), not live host directories.

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `Could not open input file` | Run from this directory; check `-w` matches mount target |
| Empty directory in container | Wrong host path — use `"$PWD"` or an absolute path |
| Permission denied | UID mismatch between host files and container user (lesson 5.6) |
| Slow file I/O on Mac | See lesson 5.8; consider `:cached` for large trees |

## Lesson acceptance

- `docker run -v "$PWD":/app -w /app php:8.3-cli php hello.php` prints from mounted code
- Editing `hello.php` on the host changes the next run without rebuild
- You can explain bind mount vs named volume intent

## What you proved

- Host stays the editor; container stays the runtime
- `-v` bind mounts are the everyday local PHP dev loop (even when you prefer `--mount` in scripts)

← [Chapter 5 — Volumes](../README.md)
