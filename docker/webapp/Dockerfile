FROM dunglas/frankenphp:1.2.5-php8.3-alpine AS base

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/download/2.5.2/install-php-extensions /usr/local/bin/

RUN install-php-extensions pdo pdo_pgsql zip intl redis

RUN apk add bash git postgresql-client zip

COPY ./docker/webapp/Caddyfile /etc/caddy/Caddyfile
COPY ./docker/webapp/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

################
FROM base AS dev

RUN apk add nodejs npm

RUN install-php-extensions xdebug

################
FROM base AS prod

COPY . /var/www/html

RUN composer install -n -q

