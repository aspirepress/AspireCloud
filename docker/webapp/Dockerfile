FROM php:8.3-fpm

MAINTAINER Sarah Savage

RUN export DEBIAN_FRONTEND=noninteractive \
    && apt-get update \
    && apt-get install -y -q \
        libpq-dev git zip libzip-dev libicu-dev

RUN docker-php-ext-install pdo \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    && pecl install xdebug-3.2.1 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
