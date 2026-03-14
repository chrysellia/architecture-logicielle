#!/bin/sh

export APP_ENV=prod
export APP_DEBUG=0

mkdir -p /var/run
mkdir -p /var/www/html/var/cache/prod
chown -R www-data:www-data /var/www/html/var

# Vider et reconstruire le cache en tant que www-data
su-exec www-data php /var/www/html/bin/console cache:clear --env=prod
su-exec www-data php /var/www/html/bin/console cache:warmup --env=prod

# Démarrer PHP-FPM en arrière-plan
php-fpm &

sleep 2

exec nginx -g "daemon off;"