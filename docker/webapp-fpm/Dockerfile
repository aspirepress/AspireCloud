FROM php:8.3-fpm-alpine AS basephp

RUN apk add \
    bash \
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
    && docker-php-ext-enable pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

FROM basephp AS devphp

RUN pecl install xdebug-3.3.0 \
&& docker-php-ext-enable xdebug

COPY ./docker/webapp-fpm/php.ini /usr/local/etc/php/php.ini

FROM basephp AS prodphp

COPY . /var/www/html

RUN composer install -n --no-dev -q


