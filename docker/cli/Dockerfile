FROM php:8.3-cli AS base

COPY --from=composer:2.8.3 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.5.2/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y bash git postgresql-client zip

RUN install-php-extensions pdo pdo_pgsql zip intl redis

COPY ./docker/cli/php.ini /usr/local/etc/php/php.ini

WORKDIR /app

################
FROM base AS dev

RUN apt update && apt install -y nodejs npm

# Xdebug exists, but is explicitly disabled for cli.  Use docker-compose.override.yml to enable.
RUN install-php-extensions xdebug
RUN sed -i 's/^/#/' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

################
# FROM base AS prod
# The cli container is strictly for dev at this time.
