SHELL := /bin/bash
DC := docker compose
CLI := $(DC) exec -T wpcli wp --allow-root --path=/var/www/html

.DEFAULT_GOAL := help

help: ## Lista os comandos
	grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

env: ## Cria .env a partir do .env.example (se nao existir)
	[ -f .env ] || cp .env.example .env

up: env ## Sobe os containers
	$(DC) up -d
	echo "Aguardando WordPress responder..."
	until curl -sf -o /dev/null http://localhost:$${WP_PORT:-8080}/wp-admin/install.php 2>/dev/null || $(DC) exec -T wpcli test -f /var/www/html/wp-load.php; do sleep 2; done
	echo "OK -> http://localhost:$${WP_PORT:-8080}"

down: ## Para os containers
	$(DC) down

restart: ## Reinicia
	$(DC) restart

logs: ## Logs do WordPress
	$(DC) logs -f wordpress

install: ## Instala e configura o WordPress do zero
	$(DC) exec -T wpcli bash /lv-docker/setup.sh

seed: ## Cadastra marcas + 6 veiculos de demonstracao
	$(CLI) eval-file /var/www/html/wp-content/plugins/plugin-lv-estoque/seeds/seed.php

wp: ## Roda um comando WP-CLI: make wp CMD="plugin list"
	$(CLI) $(CMD)

shell: ## Shell no container do WordPress
	$(DC) exec wordpress bash

cli: ## Shell no container do WP-CLI
	$(DC) exec wpcli bash

lint: ## php -l em todos os arquivos PHP
	$(DC) exec -T wordpress bash -c 'find /var/www/html/wp-content/plugins/plugin-lv-estoque /var/www/html/wp-content/themes/tema-lv -name "*.php" -print0 | xargs -0 -n1 php -l'

reset: ## APAGA o banco e os arquivos do core e reinstala tudo
	$(DC) down -v
	$(DC) up -d
	sleep 10
	$(MAKE) install

.PHONY: help env up down restart logs install seed wp shell cli lint reset
