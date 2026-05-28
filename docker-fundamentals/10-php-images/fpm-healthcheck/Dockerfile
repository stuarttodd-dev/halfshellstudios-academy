# syntax=docker/dockerfile:1
# Lesson 10.8 — php-fpm -t + FastCGI ping HEALTHCHECK
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfcgi0ldbl \
        libfcgi-bin \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php-fpm/zzz-health.conf /usr/local/etc/php-fpm.d/zzz-health.conf

WORKDIR /var/www/html
COPY public/ public/

RUN php-fpm -t

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=3 \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET \
        cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -qx pong || exit 1
