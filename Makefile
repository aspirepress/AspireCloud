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

list:
	@grep -E '^[a-zA-Z%_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | perl -ne '/^(?:.*?:)?(.*?):.*##(.*$$)/ and printf "\033[36m%-30s\033[0m %s\n", $$1, $$2'

init: check-env dirs down clean build-containers network up build reset-database reset-testing-database generate-key ## Initial configuration tasks

dirs:
	mkdir -p .cache

check-env:
	@[ -f .env ] || { cp .env.example .env; }

build-containers: ## Builds the Docker containers
	docker compose build

clean: ## Remove all Docker containers, volumes, etc
	docker compose down -v --remove-orphans
	docker compose rm -f
	rm -fr ./vendor ./node_modules

up: ## Starts the Docker containers
	docker compose up -d

build: install-php install-js build-js

down: ## Stops the Docker containers
	docker compose down

unit: ## Run unit tests
	bin/dcrun vendor/bin/pest --testsuite=Unit ${OPTS}

functional: reset-testing-database ## Run functional tests
	bin/dcrun vendor/bin/pest --testsuite=Feature ${OPTS}

test-bruno: ## Run bruno tests (yarn global add @usebruno/cli)
	cd bruno && bru run -r . --env 'Local API'

test: unit functional ## Run all tests

acceptance: ## Run acceptance tests
	bin/dcrun vendor/bin/behat -vvv ${OPTS}

quality: ## Run all quality checks
	bin/dcrun vendor/bin/phpstan --memory-limit=1G analyse ${OPTS}

quality-baseline: ## Run all static analysis checks with baseline
	bin/dcrun vendor/bin/phpstan analyse -b baseline.neon $(PHPSTAN_XDEBUG) src tests

install-php: ## Install composer dependencies
	bin/dcrun composer install

install-js:
	bin/dcrun yarn

build-js:
	bin/dcrun yarn run build

logs-%: ## View logs (follow mode) for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose logs -f $*

sh-webapp: # webapp is alpine, so we need to use sh, not sh
	docker compose exec webapp sh

sh-%: ## Execute shell for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose exec $* sh || docker compose run --rm $* sh

clear-cache: ## Clear cache
	bin/dcrun php artisan optimize:clear

helpers: ## Generate Laravel IDE helpers
	@if [ -z $(FORCE) ]; then \
		echo "*** laravel-ide-helper generates incorrect code for Laravel 11, and is not recommended at this time."; \
		echo "*** if you wish to generate helpers anyway, run 'make helpers FORCE=1'"; \
		exit 1; \
	fi
	bin/dcrun php artisan ide-helper:generate
	bin/dcrun php artisan ide-helper:meta
	bin/dcrun php artisan ide-helper:models --write --smart-reset

lint: style quality ## Check code standards conformance

check: lint test ## Run lint and unit tests

fix: fix-style ## Run automated code fixes

style: ## Run code style checks
	bin/dcrun vendor/bin/pint --test

fix-style: ## Run code style fixes
	bin/dcrun vendor/bin/pint

create-migration: ## Create a new database migration
	bin/dcrun php artisan make:migration

create-seed: ##	Create a new database seed
	bin/dcrun php artisan make:seed

migrate: ## Run database migrations
	bin/dcrun php artisan migrate --force --no-interaction

migration-rollback: ## Rollback database migrations
	bin/dcrun php artisan migrate --force --no-interaction

seed: ## Run database seeds
	bin/dcrun php artisan db:seed

generate-key: ## Generate APP_KEY environment var
	bin/dcrun php artisan key:generate

drop-database:
	bin/dcrun sh -c "export PGPASSWORD=${DB_ROOT_PASSWORD} && psql -U ${DB_ROOT_USERNAME} -h ${DB_HOST} -c 'drop database if exists ${DB_DATABASE}'"

create-database:
	bin/dcrun sh -c "export PGPASSWORD=${DB_ROOT_PASSWORD} && psql -U ${DB_ROOT_USERNAME} -h ${DB_HOST} -c 'create database ${DB_DATABASE} owner ${DB_USERNAME}'"

reset-database: drop-database create-database migrate seed ## run migrations and seeds

reset-testing-database:
	bin/dcrun meta/bin/reset-testing-database

run-psql: ## Runs Postgres on the command line using the .env file variables
	bin/dcrun sh -c "PGPASSWORD=${DB_PASSWORD} psql -U ${DB_USERNAME} -h ${DB_HOST} -p ${DB_PORT} -d ${DB_USERNAME}"

network: ## Create docker networks for aspire-net and traefik proxy (if they don't exist already)
	bin/create-external-network.sh aspire-net
	bin/create-external-network.sh traefik

rm-network: ## Remove aspire-net docker network. (traefik is not touched)
	-bin/remove-external-network.sh aspire-net

build-prod:
	docker build --target prod -t aspirepress/aspirecloud-php -f ./docker/webapp/Dockerfile .

traefik-up: network
	docker compose -f docker/traefik/docker-compose.yml up -d

traefik-down:
	docker compose -f docker/traefik/docker-compose.yml down

