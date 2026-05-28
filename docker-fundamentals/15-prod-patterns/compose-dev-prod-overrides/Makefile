COMPOSE_DEV := docker compose -f compose.yaml -f compose.dev.yaml
COMPOSE_PROD := docker compose -f compose.yaml -f compose.prod.yaml

.PHONY: dev prod-config prod-build down config-dev
dev:
	$(COMPOSE_DEV) up -d --build

prod-build:
	docker build -t ch15-app:prod-demo .

prod-config: prod-build
	$(COMPOSE_PROD) config

config-dev:
	$(COMPOSE_DEV) config

down:
	$(COMPOSE_DEV) down
