COMPOSE_DEV  := docker compose -f compose.yaml -f compose.dev.yaml --env-file .env
COMPOSE_PROD := docker compose -f compose.yaml -f compose.prod.yaml --env-file .env.prod.example
HTTP_PORT    ?= 8080

.PHONY: dev prod-up prod-config ci-check down

dev:
	$(COMPOSE_DEV) up -d --build

prod-up:
	docker build --target runtime -t ch15-split-exercise:release .
	$(COMPOSE_PROD) up -d

prod-config:
	$(COMPOSE_PROD) config

ci-check:
	$(COMPOSE_PROD) config -q

down:
	$(COMPOSE_DEV) down 2>/dev/null || true
	$(COMPOSE_PROD) down 2>/dev/null || true
