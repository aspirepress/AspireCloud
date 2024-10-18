.PHONY: *

# Set additional parameters for command
OPTS=

list:
	@grep -E '^[a-zA-Z%_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: ## Builds Composer dependencies
	docker compose build

clean: ## Cleans up the project
	docker compose down -v --remove-orphans
	docker compose rm -f
	rm -fr ./vendor

install-composer: ## Install composer dependencies
	docker compose run --rm cli bash -c "composer install"

create-migration: ## Create a new database migration
	docker compose run --rm cli vendor/bin/phinx create ${OPTS} -c phinx.php

create-seed: ##	Create a new database seed
	docker compose run --rm cli vendor/bin/phinx seed:create ${OPTS} -c phinx.php
