FROM php:8.3-fpm-alpine

MAINTAINER Sarah Savage


RUN export DEBIAN_FRONTEND=noninteractive \
    && apk update \
    && apk add \
    linux-headers \
            libpq-dev \
            git \
            zip \
            libzip-dev \
            icu-dev \
            postgresql-client \
            $PHPIZE_DEPS


RUN docker-php-ext-install pdo \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    && pecl install xdebug-3.3.0 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
