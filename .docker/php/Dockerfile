FROM php:8.0-apache

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && a2enmod rewrite && service apache2 restart \
    && docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql \
    && apt-get -y update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl