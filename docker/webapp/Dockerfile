FROM dunglas/frankenphp:1.4.1-php8.4.3-bookworm AS base

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions /usr/local/bin/

# yes, vim, even on prod.  sudo is disabled anyway, so it can't do much damage.
RUN apt update && apt install -y bash vim zip

RUN install-php-extensions pdo pdo_pgsql zip intl opcache redis

RUN cp /etc/caddy/Caddyfile /etc/caddy/Caddyfile.bak

COPY ./docker/webapp/Caddyfile /etc/caddy/Caddyfile
COPY ./docker/webapp/php.ini /usr/local/etc/php/php.ini

# frankenphp sets XDG_CONFIG_HOME=/config and XDG_DATA_HOME=/data, and I won't change these in case they're hardwired

RUN useradd --create-home --shell /bin/bash app \
    && chown -R app:app /config /data \
    && apt update \
    && apt install -y nodejs npm postgresql-client

WORKDIR /app

################
FROM base AS dev

RUN apt update && apt install -y git

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

RUN npm ci && rm -rf public/build && npm run build

USER root

RUN apt purge -y nodejs npm

USER app
