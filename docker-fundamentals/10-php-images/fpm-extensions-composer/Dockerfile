# Lesson 10.3 — smallest useful custom PHP image: official FPM + pdo_mysql
FROM php:8.3-fpm-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
        mariadb-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
