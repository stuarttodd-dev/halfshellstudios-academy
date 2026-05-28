# Code in the image, data in the volume

**Course page:** [Code in the image, data in the volume](https://docker.learnio.dev/learn/sections/chapter-volumes/volumes-code-in-the-image-data-in-the-volume)

Worked example for chapter 5 — application **code** ships in the image; **durable state** lives in named volumes. Bind mounts of source are a dev convenience, not production.

## The rule

| In the image | In a named volume |
| ------------ | ----------------- |
| PHP app, `vendor/`, `public/` | Database files (`/var/lib/mysql`) |
| Extensions, FPM config | Uploads / shared runtime files (if persisted) |
| `.env.example` (docs only) | Secrets from orchestrator — not git |

| Dev-only (bind mount) | Not in prod compose |
| --------------------- | ------------------- |
| `.:/var/www/html` for fast edits | Hiding the deployed image with host source |

## What’s in this folder

| Item | Purpose |
| ---- | ------- |
| [`Dockerfile.ch5-code`](Dockerfile.ch5-code) | Tiny image with `/message.txt` baked in |
| [`compose.ch5-data.yaml`](compose.ch5-data.yaml) | MariaDB + `dbdata` — prove rows survive `down` |
| [`compose.prod-sketch.yaml`](compose.prod-sketch.yaml) | Illustrative prod: image digest, `dbdata`, `app_uploads` |
| [`compose.dev-sketch.yaml`](compose.dev-sketch.yaml) | Illustrative dev: bind mount source |
| [`demo.sh`](demo.sh) | Image vs bind mount vs volume persistence |
| [`clean.sh`](clean.sh) | `down -v` + remove demo image |

## Quick demo

```bash
chmod +x demo.sh clean.sh
./demo.sh
./clean.sh
```

## Try it (manual)

### Code from the image

```bash
docker build -f Dockerfile.ch5-code -t ch5-code-in-image .
docker run --rm ch5-code-in-image
# from-image
```

### Host file masks image (dev only)

```bash
echo "from-host" > message.txt
docker run --rm -v "$PWD/message.txt:/message.txt:ro" ch5-code-in-image cat /message.txt
# from-host
```

### Data survives in the volume

```bash
docker compose -f compose.ch5-data.yaml up -d
docker compose -f compose.ch5-data.yaml exec db mariadb -uroot -psecret app -e \
  "CREATE TABLE IF NOT EXISTS markers (id INT AUTO_INCREMENT PRIMARY KEY, tag VARCHAR(32));
   INSERT INTO markers (tag) VALUES ('in-volume');"
docker compose -f compose.ch5-data.yaml down
docker compose -f compose.ch5-data.yaml up -d
docker compose -f compose.ch5-data.yaml exec db mariadb -uroot -psecret app -e "SELECT * FROM markers;"
```

Destructive reset:

```bash
docker compose -f compose.ch5-data.yaml down -v
```

## Related examples

| Lesson | Folder |
| ------ | ------ |
| Bind mount PHP dev loop | [bind-vs-named-php](../bind-vs-named-php/) |
| MySQL named volume only | [bind-vs-named-php](../bind-vs-named-php/) (`compose.mysql.yaml`) |

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Empty DB after deploy | Accidental `down -v` or renamed volume; keep `dbdata:` stable |
| Prod serves old PHP | Prod still bind-mounting source — remove `.:/var/www/html` |
| `vendor/` missing in prod | Run `composer install` at **build** time in Dockerfile |
| Uploads vanish on recreate | Use a named volume or object storage, not container layer |

## Lesson acceptance

- `docker run --rm ch5-code-in-image` prints `from-image` without `-v`
- Bind mount changes output to `from-host`
- `markers` row survives `docker compose down` and `up`
- You can explain code vs data vs dev-only bind mounts

## What you proved

- Images carry immutable application bytes; volumes carry mutable runtime state
- Bind mounts mask image files — powerful in dev, wrong default for production

← [Chapter 5 — Volumes](../README.md)
