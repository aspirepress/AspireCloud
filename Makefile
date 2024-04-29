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

list:
	@grep -E '^[a-zA-Z%_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


unit: ## Run unit tests
	docker compose run --rm webapp bash -c "vendor/bin/phpunit --testsuite=unit ${OPTS}"

functional: ## Run functional tests
	docker compose run --rm webapp bash -c "vendor/bin/phpunit --testsuite=functional ${OPTS}"

test: unit functional acceptance ## Run all tests

acceptance: ## Run acceptance tests
	docker compose run --rm webapp bash -c "vendor/bin/behat -vvv ${OPTS}"

quality: ## Run all quality checks
	docker compose run --rm webapp bash -c "vendor/bin/phpstan ${OPTS}"

quality-baseline: ## Run all static analysis checks with baseline
	docker compose run --rm webapp vendor/bin/phpstan analyse -b baseline.neon $(PHPSTAN_XDEBUG) src tests

assets: ## Build assets
	docker compose run --rm node bash -c "npx mix"

assets-watch: ## Watch assets
	docker compose run --rm node bash -c "npx mix watch"

install-node: ## Install node dependencies
	docker compose run --rm node bash -c "npm install"

install-composer: ## Install composer dependencies
	docker compose run --rm webapp bash -c "composer install"

logs-%: ## View logs (follow mode) for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose logs -f $*

sh-webapp: # webapp is alpine, so we need to use sh, not sh
	docker compose exec webapp sh

sh-%: ## Execute shell for the container where % is a service name (webapp, postgres, node, nginx, smtp, rabbitmq)
	docker compose exec $* sh || docker compose run --rm $* sh

clear-cache: ## Clear cache
	docker compose run --rm webapp rm -f data/cache/config-cache.php
	rm -rf public/build/

cs: ## Run code style checks
	docker compose run --rm webapp bash -c "vendor/bin/phpcs ${OPTS}"

cs-fix: ## Fix code style issues
	docker compose run --rm webapp bash -c "vendor/bin/phpcbf ${OPTS} && vendor/bin/phpcs ${OPTS}"

create-migration: ## Create a new database migration
	docker compose run --rm webapp vendor/bin/phinx create ${OPTS} -c migrations/config/phinx.php

create-seed: ##	Create a new database seed
	docker compose run --rm webapp vendor/bin/phinx seed:create ${OPTS} -c migrations/config/phinx.php

migrate: ## Run database migrations
	docker compose run --rm webapp vendor/bin/phinx migrate -c migrations/config/phinx.php

migration-rollback: ## Rollback database migrations
	docker compose run --rm webapp vendor/bin/phinx rollback -e development -c migrations/config/phinx.php

run-seeds: ## Run database seeds
	docker compose run --rm webapp vendor/bin/phinx seed:run -c migrations/config/phinx.php

devmode-enable: ## Enable the PHP development mode
	docker compose run --rm webapp composer development-enable

devmode-disable: ## Disable the PHP development mode
	docker compose run --rm webapp composer development-disable
