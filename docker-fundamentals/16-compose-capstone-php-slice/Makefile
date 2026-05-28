.PHONY: smoke up down ps config

smoke:
	docker compose up -d --wait --build
	curl -fsS http://127.0.0.1:8082/healthz.php
	curl -fsS http://127.0.0.1:8082/
	docker compose ps

up:
	docker compose up -d --build

down:
	docker compose down

ps:
	docker compose ps

config:
	docker compose config --quiet && echo "compose parses"
