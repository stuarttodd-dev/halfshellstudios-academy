# syntax=docker/dockerfile:1

FROM php:8.3-fpm-bookworm AS runtime

WORKDIR /var/www/html

RUN docker-php-ext-install opcache \
 && mkdir -p storage/logs

COPY docker/php-fpm/zzz-app-pool.conf /usr/local/etc/php-fpm.d/zzz-app-pool.conf
COPY public/ ./public/

RUN chown -R www-data:www-data storage public \
 && php-fpm -t

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

FROM runtime AS dev

USER root
RUN echo "opcache.enable=0" > /usr/local/etc/php/conf.d/zz-dev.ini \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && rm -rf /tmp/pear
USER www-data
