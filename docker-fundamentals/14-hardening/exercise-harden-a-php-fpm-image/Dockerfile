# syntax=docker/dockerfile:1
# Exercise 14.9 / Solution 14.10 — hardened PHP-FPM image

FROM php:8.3-fpm-bookworm

COPY docker/php-fpm/zzz-app-pool.conf /usr/local/etc/php-fpm.d/zzz-app-pool.conf

WORKDIR /var/www/html

COPY public/ ./public/

RUN mkdir -p storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache public \
 && php-fpm -t

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
