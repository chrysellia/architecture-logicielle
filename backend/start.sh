#!/bin/sh
mkdir -p /var/run
php-fpm &
sleep 2
exec nginx -g "daemon off;"