FROM php:7.2.6-cli

RUN apt-get update && apt-get install -y git zlib1g-dev
RUN docker-php-ext-install zip
RUN pecl install mongodb
RUN echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/docker-php-ext-mongodb.ini

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/bin/composer

COPY . /app

WORKDIR /app/

RUN composer install
