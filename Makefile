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

psalm:
	docker compose run --rm webapp bash -c "vendor/bin/psalm --show-info=false ${OPTS}"

css:
	docker compose run --rm node bash -c "npx tailwindcss -i ./assets/source/style.css -o ./assets/output/style.css"

css-watch:
	docker compose run --rm node bash -c "npx tailwindcss -i ./assets/source/style.css -o ./assets/output/style.css --watch"

install-node:
	docker compose run --rm node bash -c "npm install"
