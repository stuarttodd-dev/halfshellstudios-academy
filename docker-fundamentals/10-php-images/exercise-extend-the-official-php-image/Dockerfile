# syntax=docker/dockerfile:1
# Exercise 10.9 / Solution 10.10 — deps + runtime chapter 10 checkpoint

FROM composer:lts AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.3-fpm-bookworm AS runtime

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libfcgi0ldbl \
      libfcgi-bin \
      libicu-dev \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libpng-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql intl gd \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/*

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/zz-app.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm/zzz-app-pool.conf /usr/local/etc/php-fpm.d/zzz-app-pool.conf
COPY docker/php-fpm/zzz-health.conf /usr/local/etc/php-fpm.d/zzz-health.conf

WORKDIR /var/www/html
COPY . .
COPY --from=deps /app/vendor ./vendor
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && php-fpm -t

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET \
    cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -qx pong || exit 1
