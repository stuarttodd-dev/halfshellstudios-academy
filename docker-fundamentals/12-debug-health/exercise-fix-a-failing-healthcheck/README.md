# Exercise / Solution: fix a failing healthcheck

| Lesson | Course page |
| ------ | ----------- |
| Exercise 12.9 | [Fix a failing healthcheck](https://docker.learnio.dev/learn/sections/chapter-debug-health/debug-health-exercise-fix-a-failing-healthcheck) |
| Solution 12.10 | [Solution: fix a failing healthcheck](https://docker.learnio.dev/learn/sections/chapter-debug-health/debug-health-solution-fix-a-failing-healthcheck) |

Chapter 12 checkpoint: diagnose a lying FPM healthcheck, replace `CMD true` with tier-2 `cgi-fcgi` ping, prove `healthy`.

```text
inspect health log  →  fix Dockerfile + compose  →  pong + healthy
```

## Starter vs solution

| File | Role |
| ---- | ---- |
| [`Dockerfile.starter`](Dockerfile.starter) + [`compose.starter.yaml`](compose.starter.yaml) | Broken on purpose (`CMD true`, no ping config) |
| [`Dockerfile`](Dockerfile) + [`compose.yaml`](compose.yaml) | Fixed reference (solution) |

## What's fixed

| Bug | Fix |
| --- | --- |
| `healthcheck: ["CMD", "true"]` in Compose | Real `cgi-fcgi` probe |
| `HEALTHCHECK CMD true` in image | Tier-2 FPM ping |
| No `libfcgi` packages | `libfcgi0ldbl` + `libfcgi-bin` |
| Missing `zzz-health.conf` | Copy into `php-fpm.d/` |
| Short `start_period` | `20s` minimum |

## Quick demo (solution)

```bash
chmod +x demo.sh clean.sh
./demo.sh
```

Shows broken starter lying `healthy`, then fixed stack reaching `healthy`, manual `pong`, and recovery after FPM kill.

## Try it manually

```bash
# Broken (optional reproduce)
docker compose -f compose.starter.yaml up -d --build
docker inspect "$(docker compose -f compose.starter.yaml ps -q app)" --format '{{json .Config.Healthcheck}}'
docker compose -f compose.starter.yaml down

# Fixed
docker compose build app
docker compose up -d --force-recreate
sleep 25
docker compose ps
docker inspect "$(docker compose ps -q app)" --format 'health={{.State.Health.Status}}'

docker compose exec app sh -c \
  'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000'

docker compose exec app sh -c 'kill -TERM 1'
sleep 40
docker inspect "$(docker compose ps -q app)" --format 'health={{.State.Health.Status}}'
docker compose restart app

docker compose down
```

## Checklist

- [ ] Read `State.Health.Log` before fixing
- [ ] No `CMD true` in image or Compose
- [ ] `libfcgi` installed; `zzz-health.conf` in image
- [ ] `docker compose ps` shows `(healthy)`
- [ ] Manual `cgi-fcgi` returns `pong`
- [ ] Probe goes `unhealthy` when FPM is killed

## Related

| Lesson | Folder |
| ------ | ------ |
| FPM healthcheck | [10-php-images/fpm-healthcheck](../../10-php-images/fpm-healthcheck/) |
| docker exec | [exec-into-running-container](../exec-into-running-container/) |

← [Chapter 12 — Debug and health](../README.md)
