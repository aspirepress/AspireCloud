FROM php:8.4.6-cli-bookworm@sha256:076224f27d6e9201c4c2045e247a53df8affba43eacd8f474e6610bd90ad3f43 AS base

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y bash git gpg postgresql-client vim zip

RUN install-php-extensions pdo pdo_pgsql zip intl redis

COPY ./docker/cli/php.ini /usr/local/etc/php/php.ini

RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt update \
    && apt install -y nodejs \
    && npm install -g yarn

RUN groupadd --gid 100000 app \
    && useradd --uid 100000 --gid 100000 --create-home --shell /bin/bash app

WORKDIR /app

################
FROM base AS dev

RUN install-php-extensions xdebug

USER app

################
# FROM base AS prod
# The cli container is strictly for dev at this time.
