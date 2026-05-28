.PHONY: config services volumes ps logs-web logs-php up down demo

config:
	docker compose config

services:
	docker compose config --services

volumes:
	docker compose config --volumes

ps:
	docker compose ps -a

logs-web:
	docker compose logs --tail=50 -f web

logs-php:
	docker compose logs --tail=50 -f php

up:
	docker compose up -d --build

down:
	docker compose down -v

demo:
	./demo.sh
