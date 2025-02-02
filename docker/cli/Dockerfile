FROM php:8.4-cli AS base

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y bash git postgresql-client vim zip

RUN install-php-extensions pdo pdo_pgsql zip intl redis

COPY ./docker/cli/php.ini /usr/local/etc/php/php.ini

RUN useradd --create-home --shell /bin/bash app

WORKDIR /app

################
FROM base AS dev

RUN apt update && apt install -y nodejs npm

RUN install-php-extensions xdebug

USER app

################
# FROM base AS prod
# The cli container is strictly for dev at this time.
