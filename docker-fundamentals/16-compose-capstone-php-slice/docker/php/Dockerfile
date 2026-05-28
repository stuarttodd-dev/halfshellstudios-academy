FROM php:8.3-fpm-bookworm AS production

RUN apt-get update \
 && apt-get install -y --no-install-recommends $PHPIZE_DEPS libzip-dev \
 && docker-php-ext-install pdo_mysql \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apt-get purge -y --auto-remove $PHPIZE_DEPS \
 && rm -rf /var/lib/apt/lists/*

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

COPY src/ ./

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000

HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
  CMD php-fpm -t
