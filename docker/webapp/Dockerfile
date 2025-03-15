FROM dunglas/frankenphp:1.4.4-php8.4.4-bookworm@sha256:462c2d0eb8dd4fae16b7396f9873e0d52aab5c62d1abd0f1303bcf05041eed4e AS base

COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.7.14/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y bash gpg postgresql-client vim zip

RUN install-php-extensions pdo pdo_pgsql zip intl opcache redis

COPY ./docker/webapp/Caddyfile /etc/caddy/Caddyfile
COPY ./docker/webapp/php.ini /usr/local/etc/php/php.ini

RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt update && apt install -y nodejs \
    && npm install -g yarn

RUN useradd --uid 100000 --create-home --shell /bin/bash app && chown -R app:app /config /data

WORKDIR /app

################
FROM base AS dev

RUN install-php-extensions xdebug

#USER app

################
FROM base AS prod

# no workie, composer can't create /app/vendor ¯\_(ツ)_/¯
# COPY --chown=app:app . /app

COPY . /app
RUN chown -R app:app /app

USER app

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --working-dir=/app \
    && mkdir -p storage/logs storage/app/public storage/app/private storage/framework/sessions storage/framework/views storage/framework/cache/data

RUN yarn install && rm -rf public/build && yarn run build

USER root

USER app
