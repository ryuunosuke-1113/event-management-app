FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip pcntl

RUN echo "upload_max_filesize=5M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=10M" >> /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html