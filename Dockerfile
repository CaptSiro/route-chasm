FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates libcurl4-openssl-dev libicu-dev \
    && pecl install xdebug \
    && docker-php-ext-install curl pdo pdo_mysql intl \
    && docker-php-ext-enable xdebug \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* 

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/ssl.ini /usr/local/etc/php/conf.d/ssl.ini
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY . /var/www/html

WORKDIR /var/www/html
