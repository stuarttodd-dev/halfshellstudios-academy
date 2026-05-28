# The proxy owns TLS; internally plain HTTP / FastCGI

**Course page:** [The proxy owns TLS, internally plain HTTP](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/reverse-proxy-the-proxy-owns-tls-internally-plain-http)

HTTPS stops at nginx. The browser talks TLS to `web`; inside Compose, nginx reaches PHP-FPM on plain FastCGI (`app:9000`). Only `web` publishes host ports.

```text
Browser --TLS:443--> nginx (web) --plain FastCGI--> php-fpm (app) --MySQL--> db
```

## Forwarded headers

| Header | Purpose |
| ------ | ------- |
| `X-Forwarded-Proto` | `https` or `http` as the browser used |
| `X-Forwarded-Host` | Public hostname |
| `X-Forwarded-For` | Client IP (HTTPS server block) |

[`docker/nginx/default.conf`](docker/nginx/default.conf) sets `fastcgi_param HTTP_X_FORWARDED_*` on both `:80` and `:443` server blocks. Frameworks must **trust proxies only from the edge** — keep `app` off the public internet.

## Quick demo

```bash
chmod +x demo.sh clean.sh
cp .env.example .env   # optional port overrides
./demo.sh
```

Generates self-signed certs in `docker/nginx/certs/` if missing, then curls HTTP and HTTPS and checks `HTTP_X_FORWARDED_PROTO`.

## Try it manually

```bash
mkdir -p docker/nginx/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/certs/dev.key \
  -out docker/nginx/certs/dev.crt \
  -subj "/CN=localhost"

docker compose up -d
curl -s http://127.0.0.1:8080/index.php
curl -sk https://127.0.0.1:8443/index.php
nc -zv 127.0.0.1 9000 2>&1 || echo "nothing on host :9000 (expected)"
docker compose down
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | `web` publishes 80/443; `app` only `expose: 9000` |
| [`public/index.php`](public/index.php) | Prints `$_SERVER` forwarded metadata |
| [`demo.sh`](demo.sh) | Certs, up, HTTP/HTTPS checks, down |
| [`clean.sh`](clean.sh) | `docker compose down` |

## Production note

Use ACME or a cloud load balancer for real certificates. Do not copy self-signed paths to production. **Do** copy the forwarded-header pattern; the internal FastCGI hop stays plain.

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| URLs still `http://` | Missing `HTTP_X_FORWARDED_PROTO` or trusted-proxy config |
| Spoofed `X-Forwarded-Proto` | Do not publish `app:9000` to the host |
| `curl` HTTPS fails | Regenerate certs; check `docker compose logs web` |
| Works on HTTP, not HTTPS | Compare both `server` blocks in nginx config |

## Related

| Lesson | Folder |
| ------ | ------ |
| nginx + PHP-FPM in Compose | [nginx-fpm-socket](../nginx-fpm-socket/) |

← [Chapter 11 — Reverse proxy](../README.md)
