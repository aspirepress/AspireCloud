.PHONY: *

# Set additional parameters for command
OPTS=

# Set DEBUG=1 to enable xdebug
ifeq ($(origin DEBUG),undefined)
    XDEBUG :=
    PHPSTAN_XDEBUG :=
else
    XDEBUG := XDEBUG_SESSION=1
    PHPSTAN_XDEBUG := --xdebug
endif

ifneq (,$(wildcard ./.env))
    include .env
    export
endif

ifeq ($(AP_COMPOSE_STACK),frankenphp)
	COMPOSE_FILE := docker-compose.frankenphp.yml
else
	COMPOSE_FILE := docker-compose.yml
endif

list:
	@grep -E '^[a-zA-Z%_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

init: down clean build network up install-composer reset-database devmode-enable ## Initial configuration tasks

build: ## Builds the Docker containers
	docker compose -f $(COMPOSE_FILE) build

clean: ## Remove all Docker containers, volumes, etc
	docker compose -f $(COMPOSE_FILE) down -v --remove-orphans
	docker compose -f $(COMPOSE_FILE) rm -f
	rm -fr ./vendor

up: ## Starts the Docker containers
	docker compose -f $(COMPOSE_FILE) up -d

down: ## Stops the Docker containers
	docker compose -f $(COMPOSE_FILE) down

unit: ## Run unit tests
	docker compose -f $(COMPOSE_FILE) run --rm webapp bash -c "vendor/bin/phpunit --testsuite=unit ${OPTS}"

functional: ## Run functional tests
	docker compose -f $(COMPOSE_FILE) run --rm webapp bash -c "vendor/bin/phpunit --testsuite=functional ${OPTS}"

test: unit functional acceptance ## Run all tests

acceptance: ## Run acceptance tests
	docker compose -f $(COMPOSE_FILE) run --rm webapp bash -c "vendor/bin/behat -vvv ${OPTS}"

quality: ## Run all quality checks
	docker compose -f $(COMPOSE_FILE) run --rm webapp bash -c "vendor/bin/phpstan ${OPTS}"

quality-baseline: ## Run all static analysis checks with baseline
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phpstan analyse -b baseline.neon $(PHPSTAN_XDEBUG) src tests

install-composer: ## Install composer dependencies
	docker compose -f $(COMPOSE_FILE) run --rm webapp bash -c "composer install"

logs-%: ## View logs (follow mode) for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose -f $(COMPOSE_FILE) logs -f $*

sh-webapp: # webapp is alpine, so we need to use sh, not sh
	docker compose -f $(COMPOSE_FILE) exec webapp sh

sh-%: ## Execute shell for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose -f $(COMPOSE_FILE) exec $* sh || docker compose -f $(COMPOSE_FILE) run --rm $* sh

clear-cache: ## Clear cache
	docker compose -f $(COMPOSE_FILE) run --rm webapp rm -f data/cache/config-cache.php
	rm -rf public/build/

check: cs-fix quality test ## Check all quality and test elements

cs: ## Run code style checks
	docker compose -f $(COMPOSE_FILE) run --rm webapp bash -c "vendor/bin/phpcs ${OPTS}"

cs-fix: ## Fix code style issues
	docker compose -f $(COMPOSE_FILE) run --rm webapp bash -c "vendor/bin/phpcbf ${OPTS} && vendor/bin/phpcs ${OPTS}"

create-migration: ## Create a new database migration
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx create ${OPTS} -c db/phinx.php

create-seed: ##	Create a new database seed
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx seed:create ${OPTS} -c db/phinx.php

migrate: ## Run database migrations
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx migrate -c db/phinx.php

migration-rollback: ## Rollback database migrations
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx rollback -e development -c db/phinx.php

seed: ## Run database seeds
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx seed:run -c db/phinx.php

devmode-enable: ## Enable the PHP development mode
	docker compose -f $(COMPOSE_FILE) run --rm webapp composer development-enable

devmode-disable: ## Disable the PHP development mode
	docker compose -f $(COMPOSE_FILE) run --rm webapp composer development-disable

_empty-database: # internal target to empty database
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx migrate -c db/phinx.php -t 0

migrate-testing: ## Run database migrations
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx migrate -e testing -c db/phinx.php

seed-testing: ## Run database seeds
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx seed:run -e testing -c db/phinx.php

_empty-testing-database: # internal target to empty database
	docker compose -f $(COMPOSE_FILE) run --rm webapp vendor/bin/phinx migrate -e testing -c db/phinx.php -t 0

reset-database: _empty-database migrate seed ## Clean database, run migrations and seeds

reset-testing-database: _empty-testing-database migrate-testing seed-testing

run-pgsql: ## Runs Postgres on the command line using the .env file variables
	docker compose -f $(COMPOSE_FILE) run --rm webapp sh -c "export PGPASSWORD=${DB_PASS} && psql -U ${DB_USER} -h ${DB_HOST} -d ${DB_NAME}"

network: ## Create docker networks for app and traefik proxy (if they don't exist already)
	bin/create-external-network.sh wp-services
	bin/create-external-network.sh traefik

rm-network: ## Remove application docker network. (traefik
	-bin/remove-external-network.sh wp-services

build-prod:
	docker build --target prodphp -t aspirepress/aspirecloud-php -f ./docker/webapp/Dockerfile .

traefik-up: network
	docker compose -f docker/traefik/docker-compose.yml up -d

traefik-down:
	docker compose -f docker/traefik/docker-compose.yml down

