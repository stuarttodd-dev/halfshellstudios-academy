#!/bin/sh
set -e
echo "uid=$(id -u) user=$(id -un)"
touch storage/logs/hardening-test.log
echo "wrote storage/logs/hardening-test.log"
php-fpm -t
