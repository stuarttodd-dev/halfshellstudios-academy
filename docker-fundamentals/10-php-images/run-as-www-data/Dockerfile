# syntax=docker/dockerfile:1
# Lesson 10.7 — chown writable paths, then USER www-data
FROM php:8.3-fpm-bookworm

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
