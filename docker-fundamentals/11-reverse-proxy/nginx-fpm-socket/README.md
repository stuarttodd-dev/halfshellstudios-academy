# nginx + PHP-FPM: TCP and Unix socket FastCGI

| Lesson | Entry point |
| ------ | ----------- |
| [nginx in front of PHP-FPM in Compose](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/reverse-proxy-nginx-in-front-of-php-fpm-in-compose) | `compose.yaml` + [`smoke.sh`](smoke.sh) |
| [curl smoke through the proxy](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/reverse-proxy-curl-smoke-through-the-proxy) | [`demo-curl-smoke.sh`](demo-curl-smoke.sh) |
| [Security headers and hide server tokens](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/reverse-proxy-security-headers-and-hide-server-tokens) | [`demo-security-headers.sh`](demo-security-headers.sh) + [`compose.https.yaml`](compose.https.yaml) |
| [FastCGI sockets vs TCP ports](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/reverse-proxy-fastcgi-sockets-vs-tcp-ports) | [`demo-fastcgi.sh`](demo-fastcgi.sh) |

Match FPM `listen` to nginx `fastcgi_pass`:

| FPM `listen` | nginx `fastcgi_pass` | Compose file |
|--------------|----------------------|--------------|
| `9000` | `app:9000` | [`compose.yaml`](compose.yaml) |
| `/var/run/php/php-fpm.sock` | `unix:/var/run/php/php-fpm.sock` | [`compose.socket.yaml`](compose.socket.yaml) |

Port `9000` is FastCGI, not HTTP — do not `curl http://app:9000`.

## Quick demo (lesson 11.7 — curl smoke)

```bash
chmod +x demo-curl-smoke.sh up.sh down.sh smoke.sh
./demo-curl-smoke.sh
```

Runs `compose up`, HTTP smoke (`curl -i`, status code, `/` front controller), `--resolve app.localhost`, confirms host `:9000` is closed, and a CI one-liner.

## Quick demo (lesson 11.8 — security headers)

```bash
chmod +x demo-security-headers.sh
./demo-security-headers.sh
```

HTTPS stack with `server_tokens off`, baseline headers, HSTS on `:443` only, and `curl -I` verification. Uses [`compose.https.yaml`](compose.https.yaml) and [`docker/nginx/snippets/security-headers.conf`](docker/nginx/snippets/security-headers.conf).

## Quick demo (lesson 11.4)

```bash
chmod +x demo-fastcgi.sh up.sh down.sh smoke.sh
./demo-fastcgi.sh
```

## TCP mode (default)

```bash
docker compose -f compose.yaml up -d
curl -s http://127.0.0.1:8080/index.php
docker compose exec web sh -c 'nc -zv app 9000 2>&1 | head -1'
```

## Socket mode

```bash
docker compose -f compose.socket.yaml up -d
docker compose -f compose.socket.yaml exec app ls -l /var/run/php/
curl -s http://127.0.0.1:8080/index.php
```

Uses shared volume `php-socket` at `/var/run/php` and [`docker/fpm/zz-listen-socket.conf`](docker/fpm/zz-listen-socket.conf).

## Mismatch exercise (lesson 11.6 preview)

With socket mode running, temporarily point nginx at `app:9000` while FPM listens on the socket — expect **502 Bad Gateway**. Restore matching configs before continuing.

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | TCP: `default-tcp.conf`, `expose: 9000` on `app` |
| [`compose.socket.yaml`](compose.socket.yaml) | Unix socket + `php-socket` volume |
| [`docker/nginx/default-tcp.conf`](docker/nginx/default-tcp.conf) | `fastcgi_pass app:9000` |
| [`docker/nginx/default-socket.conf`](docker/nginx/default-socket.conf) | `fastcgi_pass unix:…` |
| [`docker/nginx/default.conf`](docker/nginx/default.conf) | Same as TCP (lesson 11.3 alias) |
| [`docker/fpm/zz-listen-socket.conf`](docker/fpm/zz-listen-socket.conf) | FPM pool socket listen |
| [`public/index.php`](public/index.php) | Prints `FastCGI via tcp` or `socket` |
| [`compose.https.yaml`](compose.https.yaml) | TLS + security headers (lesson 11.8) |
| [`docker/nginx/snippets/security-headers.conf`](docker/nginx/snippets/security-headers.conf) | Baseline proxy headers |
| [`docker/nginx/nginx.conf`](docker/nginx/nginx.conf) | `server_tokens off` |
| [`docker/nginx/default-https.conf`](docker/nginx/default-https.conf) | HTTP + HTTPS vhosts with HSTS |
| [`demo-curl-smoke.sh`](demo-curl-smoke.sh) | Lesson 11.7 — curl smoke through the proxy |
| [`demo-security-headers.sh`](demo-security-headers.sh) | Lesson 11.8 — headers + `server_tokens` |
| [`demo-fastcgi.sh`](demo-fastcgi.sh) | Automated TCP + socket walkthrough |
| [`up.sh`](up.sh) / [`down.sh`](down.sh) / [`smoke.sh`](smoke.sh) | TCP stack helpers |

## `ports` vs `expose`

| Service | Host access |
| ------- | ----------- |
| `web` | `127.0.0.1:8080` → container `:80` |
| `app` | Internal only — never publish `9000:9000` to the laptop |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `502` after socket switch | `fastcgi_pass` must use `unix:…` path that exists in **both** containers |
| Socket file missing | Check `php-socket` volume on `web` and `app`; `docker compose logs app` |
| Permission denied on `.sock` | Align `listen.owner` / `listen.group` with nginx user, or demo `listen.mode` |
| `File not found` | Shared `./` mount and `SCRIPT_FILENAME` / `root` (lesson 11.3) |
| Port 8080 busy | `HTTP_PORT` in `.env` |

← [Chapter 11 — Reverse proxy](../README.md)
