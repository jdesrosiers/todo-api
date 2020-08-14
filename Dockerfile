FROM composer:1.6.5 AS composer

FROM php:7.2.6
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y zlib1g-dev libssl-dev && docker-php-ext-install zip
RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN apt-get update && apt-get install -y git

COPY . /app

WORKDIR /app/

RUN composer install
