# Healthcheck for PHP-FPM

**Course page:** [Healthcheck for PHP-FPM](https://docker.learnio.dev/learn/sections/chapter-php-images/php-images-healthcheck-for-php-fpm)

`Up` is not enough. FPM-native probes:

| Tier | Probe | Proves |
| ---- | ----- | ------ |
| 1 | `php-fpm -t` | Pool config parses |
| 2 | `cgi-fcgi` → `ping.path` | FastCGI listener answers (`pong`) |

Do **not** `curl http://127.0.0.1:9000` — port 9000 is FastCGI, not HTTP.

## Quick demo

```bash
chmod +x demo.sh clean.sh
cp .env.example .env   # optional HTTP_PORT
./demo.sh
```

## Try it manually

```bash
docker build -t ch10-fpm-health:local .
docker run --rm ch10-fpm-health:local php-fpm -t

docker run -d --name ch10-fpm-health-test ch10-fpm-health:local
sleep 15
docker inspect ch10-fpm-health-test --format '{{.State.Health.Status}}'

docker exec ch10-fpm-health-test sh -c \
  'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000'

docker compose up -d
curl http://127.0.0.1:8080/
docker compose down
```

## What's in this folder

| Item | Purpose |
| ---- | ------- |
| [`docker/php-fpm/zzz-health.conf`](docker/php-fpm/zzz-health.conf) | `ping.path` / `ping.response` |
| [`Dockerfile`](Dockerfile) | `libfcgi0ldbl`, `RUN php-fpm -t`, `HEALTHCHECK` |
| [`compose.yaml`](compose.yaml) | `app` healthcheck + `web` `service_healthy` |
| [`demo.sh`](demo.sh) | Build, health state, ping, Compose smoke |

## HEALTHCHECK (image)

```dockerfile
HEALTHCHECK ... CMD SCRIPT_NAME=/ping ... cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -qx pong
```

## Related

| Lesson | Folder |
| ------ | ------ |
| Run as www-data | [run-as-www-data](../run-as-www-data/) |
| php.ini / FPM pools | Course lesson 10.6 |
| Running vs healthy | Chapter 12 |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `cgi-fcgi: not found` | Install `libfcgi-bin` (and `libfcgi0ldbl`) on bookworm |
| Stuck `starting` | Increase `start_period` |
| Empty ping | Check `zzz-health.conf` loaded; FPM listening on 9000 |
| `healthy` but 502 | HTTP/nginx layer — separate smoke test |

← [Chapter 10 — PHP images](../README.md)
