.PHONY: *

OPTS=

unit:
	docker compose run --rm webapp bash -c "vendor/bin/phpunit --testsuite=unit ${OPTS}"

functional:
	docker compose run --rm webapp bash -c "vendor/bin/phpunit --testsuite=functional ${OPTS}"

test:
	docker compose run --rm webapp bash -c "vendor/bin/phpunit ${OPTS}"

acceptance:
	docker compose run --rm webapp bash -c "vendor/bin/behat -vvv ${OPTS}"

stan:
	docker compose run --rm webapp bash -c "vendor/bin/phpstan ${OPTS}"

assets:
	docker compose run --rm node bash -c "npx mix"

assets-watch:
	docker compose run --rm node bash -c "npx mix watch"

install-node:
	docker compose run --rm node bash -c "npm install"
