# syntax=docker/dockerfile:1

FROM php:8.3-fpm-bookworm AS build

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

FROM php:8.3-fpm-bookworm AS runtime

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libicu-dev \
      $PHPIZE_DEPS \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql intl \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/php/zz-app.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-www.conf

COPY --from=build --chown=www-data:www-data /app/vendor ./vendor
COPY --chown=www-data:www-data . .

RUN mkdir -p storage bootstrap/cache app/storage \
 && ln -sf app/public public \
 && chown -R www-data:www-data storage bootstrap/cache app/storage \
 && chmod +x artisan \
 && php-fpm -t

USER www-data
EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD php-fpm -t || exit 1

CMD ["php-fpm"]

FROM runtime AS dev

USER root
RUN pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && rm -rf /tmp/pear
USER www-data
