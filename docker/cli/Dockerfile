FROM php:8.4.5-cli-bookworm@sha256:42c541dc5c5847883b54ac3bcaac74713431506db8ebcead3a445004a68d1836 AS base

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y bash git postgresql-client vim zip

RUN install-php-extensions pdo pdo_pgsql zip intl redis

COPY ./docker/cli/php.ini /usr/local/etc/php/php.ini

RUN useradd --uid 100000 --create-home --shell /bin/bash app

WORKDIR /app

################
FROM base AS dev

RUN apt update && apt install -y nodejs npm && npm install -g yarn

RUN install-php-extensions xdebug

USER app

################
# FROM base AS prod
# The cli container is strictly for dev at this time.
