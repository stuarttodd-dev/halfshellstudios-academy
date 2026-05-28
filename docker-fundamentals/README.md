# Docker fundamentals

Learn what containers are, how Docker works, and how to use images, containers, volumes, and networks in real projects.

**Course:** [Docker fundamentals](https://docker.learnio.dev/)

## Chapters

| Chapter | Folder | Course chapter |
| ------- | ------ | -------------- |
| 1 — Install Docker | [1-install-docker](1-install-docker/) | [chapter-why-containers](https://docker.learnio.dev/learn/sections/chapter-why-containers/) |
| 2 — Images and layers | [2-images-layers](2-images-layers/) | [chapter-images-layers](https://docker.learnio.dev/learn/sections/chapter-images-layers/) |
| 3 — Dockerfile core | [3-dockerfile-core](3-dockerfile-core/) | [chapter-dockerfile-core](https://docker.learnio.dev/learn/sections/chapter-dockerfile-core/) |
| 4 — CLI workflow | [4-cli-workflow](4-cli-workflow/) | [chapter-cli-workflow](https://docker.learnio.dev/learn/sections/chapter-cli-workflow/) |
| 5 — Volumes | [5-volumes](5-volumes/) | [chapter-volumes](https://docker.learnio.dev/learn/sections/chapter-volumes/) |
| 6 — Networks | [6-networks](6-networks/) | [chapter-networks](https://docker.learnio.dev/learn/sections/chapter-networks/) |
| 7 — Compose basics | [7-compose-basics](7-compose-basics/) | [chapter-compose-basics](https://docker.learnio.dev/learn/sections/chapter-compose-basics/) |
| 8 — Env and secrets | [8-env-secrets](8-env-secrets/) | [chapter-env-secrets](https://docker.learnio.dev/learn/sections/chapter-env-secrets/) |
| 9 — Multi-stage | [9-multi-stage](9-multi-stage/) | [chapter-multi-stage](https://docker.learnio.dev/learn/sections/chapter-multi-stage/) |
| 10 — PHP images | [10-php-images](10-php-images/) | [chapter-php-images](https://docker.learnio.dev/learn/sections/chapter-php-images/) |
| 11 — Reverse proxy | [11-reverse-proxy](11-reverse-proxy/) | [chapter-reverse-proxy](https://docker.learnio.dev/learn/sections/chapter-reverse-proxy/) |
| 12 — Debug and health | [12-debug-health](12-debug-health/) | [chapter-debug-health](https://docker.learnio.dev/learn/sections/chapter-debug-health/) |
| 13 — CI and registry | [13-ci-registry](13-ci-registry/) | [chapter-ci-registry](https://docker.learnio.dev/learn/sections/chapter-ci-registry/) |
| 14 — Hardening | [14-hardening](14-hardening/) | [chapter-hardening](https://docker.learnio.dev/learn/sections/chapter-hardening/) |
| 15 — Prod patterns | [15-prod-patterns](15-prod-patterns/) | [chapter-prod-patterns](https://docker.learnio.dev/learn/sections/chapter-prod-patterns/) |
| 16 — Capstone: PHP stack in Compose | [16-compose-capstone-php-slice](16-compose-capstone-php-slice/) | [chapter-compose-capstone-php-slice](https://docker.learnio.dev/learn/sections/chapter-compose-capstone-php-slice/) |

## Chapter 1 examples

| Lesson | Folder |
| ------ | ------ |
| Solution: add a healthcheck | [1-install-docker/healthcheck-nginx](1-install-docker/healthcheck-nginx/) |

## Chapter 2 examples

| Lesson | Folder |
| ------ | ------ |
| Pull, run, and smoke-test an image | [2-images-layers/nginx-pull-run](2-images-layers/nginx-pull-run/) |
| Build, tag, and inspect an image locally | [2-images-layers/img-min-build-tag-inspect](2-images-layers/img-min-build-tag-inspect/) |
| docker history, image size, layer dedup | [2-images-layers/history-size-layer-dedup](2-images-layers/history-size-layer-dedup/) |
| Tags vs digests | [2-images-layers/tags-vs-digests](2-images-layers/tags-vs-digests/) |
| Fat bases, slim bases, and distroless | [2-images-layers/fat-slim-distroless-bases](2-images-layers/fat-slim-distroless-bases/) |
| Build context and .dockerignore | [2-images-layers/dockerignore-context](2-images-layers/dockerignore-context/) |
| Exercise / solution: pull and smoke-test | [2-images-layers/exercise-pull-smoke](2-images-layers/exercise-pull-smoke/) |

## Chapter 3 examples

| Lesson | Folder |
| ------ | ------ |
| Minimal static site Dockerfile | [3-dockerfile-core/static-site-six-line](3-dockerfile-core/static-site-six-line/) |
| Exercise / solution: static site image | [3-dockerfile-core/static-site-six-line](3-dockerfile-core/static-site-six-line/) |
| PHP-FPM Dockerfile from scratch | [3-dockerfile-core/php-fpm-from-scratch](3-dockerfile-core/php-fpm-from-scratch/) |
| RUN, COPY, and WORKDIR discipline | [3-dockerfile-core/run-copy-workdir-demo](3-dockerfile-core/run-copy-workdir-demo/) |
| Order layers for cache | [3-dockerfile-core/cache-order-demo](3-dockerfile-core/cache-order-demo/) |

## Chapter 4 examples

| Lesson | Folder |
| ------ | ------ |
| docker exec and one-off commands | [4-cli-workflow/exec-one-off-demo](4-cli-workflow/exec-one-off-demo/) |
| Exercise / solution: CLI inner loop | [4-cli-workflow/exercise-cli-inner-loop](4-cli-workflow/exercise-cli-inner-loop/) |

## Chapter 5 examples

| Lesson | Folder |
| ------ | ------ |
| Mount a PHP project for local dev | [5-volumes/bind-vs-named-php](5-volumes/bind-vs-named-php/) |
| MySQL with a named volume | [5-volumes/bind-vs-named-php](5-volumes/bind-vs-named-php/) (`compose.mysql.yaml`) |
| Code in the image, data in the volume | [5-volumes/code-in-image-data-in-volume](5-volumes/code-in-image-data-in-volume/) |
| Exercise / solution: named volume for MySQL | [5-volumes/exercise-named-volume-mysql](5-volumes/exercise-named-volume-mysql/) |

## Chapter 6 examples

| Lesson | Folder |
| ------ | ------ |
| Two containers on one user-defined network | [6-networks/two-service-dns](6-networks/two-service-dns/) |
| PHP, MySQL, and Redis by service name | [6-networks/two-service-dns](6-networks/two-service-dns/) (`compose.php-mysql-redis.yaml`) |
| Container DNS without hard-coded IPs | [6-networks/container-dns-demo](6-networks/container-dns-demo/) |
| Publishing ports vs internal traffic | [6-networks/publish-vs-internal](6-networks/publish-vs-internal/) |
| Connection refused: nothing listening | [6-networks/connection-refused-nothing-listening](6-networks/connection-refused-nothing-listening/) |
| Solution: two services talk over DNS | [6-networks/two-service-dns](6-networks/two-service-dns/) (`solution-demo.sh`) |

## Chapter 7 examples

| Lesson | Folder |
| ------ | ------ |
| nginx and PHP-FPM in Compose | [7-compose-basics/compose-two-service](7-compose-basics/compose-two-service/) |
| MySQL, Redis, Mailhog with profiles | [7-compose-basics/compose-two-service](7-compose-basics/compose-two-service/) (`compose.stack.yaml`) |
| Dev overrides with compose.override.yaml | [7-compose-basics/compose-override-dev](7-compose-basics/compose-override-dev/) |
| compose config, ps, logs, and run | [7-compose-basics/compose-config-ps-logs-run](7-compose-basics/compose-config-ps-logs-run/) |

## Chapter 9 examples

| Lesson | Folder |
| ------ | ------ |
| COPY --from and named stages | [9-multi-stage/copy-from-named-stages](9-multi-stage/copy-from-named-stages/) |
| Shrink a bloated PHP image | [9-multi-stage/shrink-bloated-php](9-multi-stage/shrink-bloated-php/) |
| Exercise / Solution: multi-stage PHP image | [9-multi-stage/exercise-multi-stage-php-image](9-multi-stage/exercise-multi-stage-php-image/) |

## Chapter 10 examples

| Lesson | Folder |
| ------ | ------ |
| Official image plus one extension | [10-php-images/fpm-extensions-composer](10-php-images/fpm-extensions-composer/) |
| Install pdo_mysql, redis, intl, gd | [10-php-images/fpm-extensions-composer](10-php-images/fpm-extensions-composer/) (`Dockerfile.bookworm`) |
| Run as www-data, not root | [10-php-images/run-as-www-data](10-php-images/run-as-www-data/) |
| Healthcheck for PHP-FPM | [10-php-images/fpm-healthcheck](10-php-images/fpm-healthcheck/) |
| Exercise / Solution: extend the official PHP image | [10-php-images/exercise-extend-the-official-php-image](10-php-images/exercise-extend-the-official-php-image/) |

## Chapter 11 examples

| Lesson | Folder |
| ------ | ------ |
| nginx in front of PHP-FPM in Compose | [11-reverse-proxy/nginx-fpm-socket](11-reverse-proxy/nginx-fpm-socket/) |
| FastCGI sockets vs TCP ports | [11-reverse-proxy/nginx-fpm-socket](11-reverse-proxy/nginx-fpm-socket/) (`demo-fastcgi.sh`) |
| curl smoke through the proxy | [11-reverse-proxy/nginx-fpm-socket](11-reverse-proxy/nginx-fpm-socket/) (`demo-curl-smoke.sh`) |
| Security headers and hide server tokens | [11-reverse-proxy/nginx-fpm-socket](11-reverse-proxy/nginx-fpm-socket/) (`demo-security-headers.sh`) |
| Exercise / Solution: nginx plus PHP-FPM | [11-reverse-proxy/exercise-nginx-plus-php-fpm](11-reverse-proxy/exercise-nginx-plus-php-fpm/) |
| The proxy owns TLS; plain FastCGI inside | [11-reverse-proxy/tls-edge-plain-fastcgi](11-reverse-proxy/tls-edge-plain-fastcgi/) |

## Chapter 12 examples

| Lesson | Folder |
| ------ | ------ |
| docker exec into a running container | [12-debug-health/exec-into-running-container](12-debug-health/exec-into-running-container/) |
| Exercise / Solution: fix a failing healthcheck | [12-debug-health/exercise-fix-a-failing-healthcheck](12-debug-health/exercise-fix-a-failing-healthcheck/) |

## Chapter 13 examples

| Lesson | Folder |
| ------ | ------ |
| GitHub Actions build and push | [13-ci-registry/github-actions-build-and-push](13-ci-registry/github-actions-build-and-push/) |
| Tag with git SHA and pin prod to digest | [13-ci-registry/tag-with-git-sha-and-pin-prod-to-digest](13-ci-registry/tag-with-git-sha-and-pin-prod-to-digest/) |
| Smoke the image in CI before push | [13-ci-registry/smoke-the-image-in-ci-before-push](13-ci-registry/smoke-the-image-in-ci-before-push/) |
| Exercise: wire a minimal CI pipeline | [exercise-wire-a-minimal-ci-pipeline](13-ci-registry/exercise-wire-a-minimal-ci-pipeline/) |
| Solution: wire a minimal CI pipeline | [solution-wire-a-minimal-ci-pipeline](13-ci-registry/solution-wire-a-minimal-ci-pipeline/) |

## Chapter 8 examples

| Lesson | Folder |
| ------ | ------ |
| Build-time vs run-time configuration | [8-env-secrets/build-vs-runtime-demo](8-env-secrets/build-vs-runtime-demo/) |
| A .env-driven Laravel-style container | [8-env-secrets/dotenv-laravel-style](8-env-secrets/dotenv-laravel-style/) |
| Exercise / Solution: wire env without baking secrets | [8-env-secrets/exercise-wire-env-without-baking-secrets](8-env-secrets/exercise-wire-env-without-baking-secrets/) |

## Chapter 14 examples

| Lesson | Folder |
| ------ | ------ |
| USER www-data in your Dockerfile | [14-hardening/www-data-dockerfile](14-hardening/www-data-dockerfile/) |
| Exercise / Solution: harden a PHP-FPM image | [14-hardening/exercise-harden-a-php-fpm-image](14-hardening/exercise-harden-a-php-fpm-image/) |

## Chapter 15 examples

| Lesson | Folder |
| ------ | ------ |
| compose.yaml plus dev and prod overrides | [15-prod-patterns/compose-dev-prod-overrides](15-prod-patterns/compose-dev-prod-overrides/) |
| Bind-mount in dev, copy-only in prod | [15-prod-patterns/bind-mount-dev-copy-prod](15-prod-patterns/bind-mount-dev-copy-prod/) |
| Exercise / Solution: split dev and prod Compose | [15-prod-patterns/exercise-split-dev-and-prod-compose](15-prod-patterns/exercise-split-dev-and-prod-compose/) |

## Chapter 16 examples

| Lesson | Folder |
| ------ | ------ |
| Capstone scope (chapter reference) | [16-compose-capstone-php-slice](16-compose-capstone-php-slice/) |
| Exercise / Solution: stand up the capstone stack | [16-compose-capstone-php-slice/exercise-stand-up-the-capstone-stack](16-compose-capstone-php-slice/exercise-stand-up-the-capstone-stack/) |

← [Half Shell Studios Academy](../README.md)
