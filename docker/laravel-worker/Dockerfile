FROM php:8.4.5-cli-bookworm@sha256:f9410ffdc12c9ab0b6c390a56e46a13ef58558f7c2abda1bc1bb3291a7d57452 AS base

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y bash git postgresql-client zip

RUN install-php-extensions pdo pdo_pgsql zip intl opcache redis

COPY ./docker/cli/php.ini /usr/local/etc/php/php.ini

RUN groupadd --gid 100000 app \
    && useradd --uid 100000 --gid 100000 --create-home --shell /bin/bash app

WORKDIR /app

################
FROM base AS dev

RUN install-php-extensions xdebug

USER app

################
FROM base AS prod

# no workie, composer can't create /app/vendor ¯\_(ツ)_/¯
# COPY --chown=app:app . /app

COPY . /app
RUN chown -R app:app /app

USER app

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --working-dir=/app \
    && mkdir -p storage/logs storage/app/public storage/app/private storage/framework/sessions storage/framework/views storage/framework/cache/data
