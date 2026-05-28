# Exercise / Solution: nginx plus PHP-FPM

| Lesson | Course page |
| ------ | ----------- |
| **Solution 11.10** | [Solution: nginx plus PHP-FPM](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/reverse-proxy-solution-nginx-plus-php-fpm) |
| Exercise 11.9 | [Exercise: nginx plus PHP-FPM](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/reverse-proxy-exercise-nginx-plus-php-fpm) |

Both lessons point at this folder (`github_example: 11-reverse-proxy/exercise-nginx-plus-php-fpm`).

Chapter 11 checkpoint: one `web` + `app` stack with TLS at the edge, FastCGI routing, curl smoke, forwarded headers, and security headers.

```text
curl :8080 / :8443  →  nginx (web)  →  FastCGI app:9000  →  php-fpm (app)
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`compose.yaml`](compose.yaml) | `web` publishes 80/443; `app` exposes 9000 only |
| [`docker/nginx/default.conf`](docker/nginx/default.conf) | HTTP + HTTPS, `try_files`, FastCGI, forwarded headers |
| [`docker/nginx/snippets/security-headers.conf`](docker/nginx/snippets/security-headers.conf) | Baseline proxy headers (11.8) |
| [`public/index.php`](public/index.php) | Smoke output + forwarded header display |

## Quick demo (solution 11.10)

```bash
chmod +x demo.sh solution-demo.sh clean.sh
cp .env.example .env   # optional port overrides
./solution-demo.sh
```

Runs the full solution walkthrough: compose config, HTTP/HTTPS smoke, security headers, and optional 502 reproduction. `./demo.sh` is the same script.

## Try it manually

```bash
mkdir -p docker/nginx/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/certs/dev.key \
  -out docker/nginx/certs/dev.crt \
  -subj "/CN=localhost"

docker compose config --quiet
docker compose up -d
docker compose exec web nginx -t

curl -fsS http://127.0.0.1:8080/index.php
curl -fsSk https://127.0.0.1:8443/index.php
curl -skI https://127.0.0.1:8443/index.php | grep -iE 'strict-transport|x-content-type|server:'

docker compose down
```

## Checklist

- [ ] Only `web` publishes host ports
- [ ] `fastcgi_pass app:9000`
- [ ] Shared `public/` mount on `web` and `app`
- [ ] `curl` HTTP and HTTPS return 200 on `/index.php`
- [ ] `HTTP_X_FORWARDED_PROTO=https` on TLS vhost
- [ ] HSTS + baseline headers on HTTPS
- [ ] `nginx -t` passes inside `web`

## Related

| Lesson | Folder |
| ------ | ------ |
| nginx + FPM in Compose | [nginx-fpm-socket](../nginx-fpm-socket/) |
| TLS at edge | [tls-edge-plain-fastcgi](../tls-edge-plain-fastcgi/) |
| curl smoke | [nginx-fpm-socket](../nginx-fpm-socket/) (`demo-curl-smoke.sh`) |
| Security headers | [nginx-fpm-socket](../nginx-fpm-socket/) (`demo-security-headers.sh`) |

← [Chapter 11 — Reverse proxy](../README.md)
