COMPOSE_DEV  := docker compose -f compose.yaml -f compose.dev.yaml --env-file .env
COMPOSE_PROD := docker compose -f compose.yaml -f compose.prod.yaml --env-file .env.prod.example
HTTP_PORT    ?= 8080

.PHONY: dev smoke down prod-config ci-compose

dev:
	$(COMPOSE_DEV) up -d --build

smoke:
	@echo "== bring up stack (wait for health) =="
	$(COMPOSE_DEV) up -d --build --wait
	@echo "== web: HTTP healthz =="
	curl -fsS http://127.0.0.1:$(HTTP_PORT)/healthz.php
	@echo "== web: front page =="
	curl -fsS http://127.0.0.1:$(HTTP_PORT)/ | head -1
	@echo "== db =="
	$(COMPOSE_DEV) exec -T db mysqladmin ping -h 127.0.0.1 -u$${MYSQL_USER:-capstone} -p$${MYSQL_PASSWORD:-capstone}
	$(COMPOSE_DEV) exec -T db mysql -u$${MYSQL_USER:-capstone} -p$${MYSQL_PASSWORD:-capstone} $${MYSQL_DATABASE:-capstone} \
		-e "SELECT COUNT(*) AS greetings FROM greetings;"
	@echo "== redis =="
	$(COMPOSE_DEV) exec -T redis redis-cli ping
	@echo "== app: php-fpm =="
	$(COMPOSE_DEV) exec -T app php-fpm -t
	@echo "== app: db + redis probe =="
	$(COMPOSE_DEV) exec -T app php public/db-redis-probe.php
	@echo "== worker =="
	$(COMPOSE_DEV) logs worker --tail 15
	@test "$$($(COMPOSE_DEV) ps -q worker | wc -l | tr -d ' ')" -eq 1
	@echo "== scheduler =="
	$(COMPOSE_DEV) logs scheduler --tail 15
	@test "$$($(COMPOSE_DEV) ps -q scheduler | wc -l | tr -d ' ')" -eq 1
	$(COMPOSE_DEV) ps
	@echo "smoke: all checks passed"

down:
	$(COMPOSE_DEV) down

prod-config:
	$(COMPOSE_PROD) config

ci-compose:
	$(COMPOSE_PROD) config -q
