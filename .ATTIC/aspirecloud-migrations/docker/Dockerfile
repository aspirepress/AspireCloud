FROM php:8.3-cli-alpine

RUN apk add git unzip bash

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
