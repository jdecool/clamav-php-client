FROM php:7.2-cli-alpine
MAINTAINER Jérémy DECOOL <contact@jdecool.fr>

RUN apk add --update make

RUN docker-php-ext-install sockets

RUN wget -O /usr/local/bin/composer https://getcomposer.org/composer.phar \
    && chmod +x /usr/local/bin/composer \
    && composer global require "hirak/prestissimo:^0.3"

ADD . /app
WORKDIR /app

RUN composer install
