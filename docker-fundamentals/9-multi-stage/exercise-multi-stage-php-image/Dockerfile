# syntax=docker/dockerfile:1
# Solution — exercise 9.9 / solution 9.10

FROM composer:lts AS deps

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.3-fpm-bookworm AS runtime

RUN docker-php-ext-install -j"$(nproc)" pdo_mysql opcache

WORKDIR /var/www/html

COPY . .
COPY --from=deps /app/vendor ./vendor

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
