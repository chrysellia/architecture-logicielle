#!/bin/sh
mkdir -p /var/run
mkdir -p /var/www/html/var/cache/prod
chmod -R 777 /var/www/html/var
php-fpm &
sleep 2
exec nginx -g "daemon off;"