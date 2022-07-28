FROM php:8.1-fpm-alpine

WORKDIR /var/www/html/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apk add --update tzdata

ENV TZ=America/Sao_Paulo

RUN apk add bash nano tzdata

RUN apk update --no-cache \
&& apk add \
icu-dev \
tzdata \
oniguruma-dev \
bash \
libzip-dev \
libxml2-dev

RUN docker-php-ext-install intl

RUN docker-php-ext-install pcntl

RUN docker-php-ext-install pdo_mysql

RUN docker-php-ext-install mbstring



COPY . .
