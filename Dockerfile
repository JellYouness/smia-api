FROM php:8.3-fpm-alpine

RUN apk add --no-cache git zip unzip bash icu-dev libpng-dev oniguruma-dev zlib-dev libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
