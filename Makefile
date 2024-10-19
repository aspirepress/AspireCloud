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

init: check-env dirs down clean build network up install-composer reset-database generate-key ## Initial configuration tasks

dirs:
	mkdir -p .cache

check-env:
	@[ -f .env ] || { cp .env.example .env; }

build: ## Builds the Docker containers
	docker compose build

clean: ## Remove all Docker containers, volumes, etc
	docker compose down -v --remove-orphans
	docker compose rm -f
	rm -fr ./vendor

up: ## Starts the Docker containers
	docker compose up -d

down: ## Stops the Docker containers
	docker compose down

unit: ## Run unit tests
	bin/dcrun vendor/bin/pest --testsuite=Unit ${OPTS}

functional: ## Run functional tests
	bin/dcrun vendor/bin/pest --testsuite=Feature ${OPTS}

test: unit functional ## Run all tests

acceptance: ## Run acceptance tests
	bin/dcrun vendor/bin/behat -vvv ${OPTS}

quality: ## Run all quality checks
	bin/dcrun vendor/bin/phpstan --memory-limit=1G analyse ${OPTS}

quality-baseline: ## Run all static analysis checks with baseline
	bin/dcrun vendor/bin/phpstan analyse -b baseline.neon $(PHPSTAN_XDEBUG) src tests

install-composer: ## Install composer dependencies
	bin/dcrun composer install

logs-%: ## View logs (follow mode) for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose logs -f $*

sh-webapp: # webapp is alpine, so we need to use sh, not sh
	docker compose exec webapp sh

sh-%: ## Execute shell for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose exec $* sh || docker compose run --rm $* sh

clear-cache: ## Clear cache
	bin/dcrun php artisan optimize:clear

lint: style quality ## Check code standards conformance

check: lint test ## Run lint and unit tests

fix: fix-style ## Run automated code fixes

style: ## Run code style checks
	bin/dcrun vendor/bin/php-cs-fixer check

fix-style: ## Run code style fixes
	bin/dcrun vendor/bin/php-cs-fixer fix

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

migrate-testing: ## Run database migrations
	bin/dcrun php artisan migrate --database=test --force --no-interaction

seed-testing: ## Run database seeds
	bin/dcrun php artisan db:seed --database=test

generate-key: ## Generate APP_KEY environment var
	bin/dcrun php artisan key:generate

drop-database:
	bin/dcrun sh -c "export PGPASSWORD=${DB_ROOT_PASSWORD} && psql -U ${DB_ROOT_USERNAME} -h ${DB_HOST} -c 'drop database if exists ${DB_DATABASE}'"

create-database:
	bin/dcrun sh -c "export PGPASSWORD=${DB_ROOT_PASSWORD} && psql -U ${DB_ROOT_USERNAME} -h ${DB_HOST} -c 'create database ${DB_DATABASE} owner ${DB_USERNAME}'"

reset-database: drop-database create-database migrate seed ## run migrations and seeds

reset-testing-database: migrate-testing seed-testing

run-psql: ## Runs Postgres on the command line using the .env file variables
	bin/dcrun sh -c "PGPASSWORD=${DB_PASSWORD} psql -U ${DB_USERNAME} -h ${DB_HOST} -p ${DB_PORT} -d ${DB_USERNAME}"

network: ## Create docker networks for app and traefik proxy (if they don't exist already)
	bin/create-external-network.sh wp-services
	bin/create-external-network.sh traefik

rm-network: ## Remove application docker network. (traefik
	-bin/remove-external-network.sh wp-services

build-prod:
	docker build --target prod -t aspirepress/aspirecloud-php -f ./docker/webapp/Dockerfile .

traefik-up: network
	docker compose -f docker/traefik/docker-compose.yml up -d

traefik-down:
	docker compose -f docker/traefik/docker-compose.yml down

