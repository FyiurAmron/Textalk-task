FROM php:8.0.10-fpm-alpine3.13

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions imagick memcached xdebug @composer

EXPOSE 9001
