FROM php:8.3-fpm

ARG UID=1000
ARG GID=$UID
RUN groupmod -g $GID www-data \
    && usermod -u $UID www-data

WORKDIR /var/www/html

RUN apt-get update

RUN apt-get install -y libzip-dev zip

RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN mkdir -p /var/www/.composer
RUN chown www-data:www-data -R /var/www/.composer

USER www-data
COPY --chown=www-data:www-data ./ ./
