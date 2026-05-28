# syntax=docker/dockerfile:1
# Exercise 12.9 / Solution 12.10 — fixed tier-2 FPM healthcheck

FROM php:8.3-fpm-bookworm

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libfcgi0ldbl \
      libfcgi-bin \
 && rm -rf /var/lib/apt/lists/*

COPY docker/php-fpm/zzz-app-pool.conf /usr/local/etc/php-fpm.d/zzz-app-pool.conf
COPY docker/php-fpm/zzz-health.conf /usr/local/etc/php-fpm.d/zzz-health.conf

WORKDIR /var/www/html
COPY public/ ./public/

RUN php-fpm -t

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET \
    cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -qx pong || exit 1
