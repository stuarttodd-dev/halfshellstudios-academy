FROM php:8.3-cli-bookworm
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip \
 && rm -rf /var/lib/apt/lists/*

COPY composer.json ./
RUN composer install --no-dev --no-interaction

COPY . .
CMD ["php", "-v"]
