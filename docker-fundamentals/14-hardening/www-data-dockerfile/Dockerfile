FROM php:8.3-fpm-bookworm

WORKDIR /var/www/html

RUN mkdir -p storage/logs
COPY write-test.sh .
RUN chmod +x write-test.sh
RUN chown -R www-data:www-data storage

USER www-data

CMD ["./write-test.sh"]
