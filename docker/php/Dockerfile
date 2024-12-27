FROM php:8.1-fpm-alpine

WORKDIR /var/www/app

RUN apk update && apk add --no-cache \
    libstdc++ \
    libpq-dev && \
    docker-php-ext-install pdo pdo_mysql

COPY . /var/www/app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
