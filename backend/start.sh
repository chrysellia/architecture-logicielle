#!/bin/sh

export APP_ENV=prod
export APP_DEBUG=0

mkdir -p /var/run

# Vider et reconstruire le cache
php /var/www/html/bin/console cache:clear --env=prod --no-warmup
php /var/www/html/bin/console cache:warmup --env=prod

# Démarrer PHP-FPM en arrière-plan
php-fpm &

sleep 2

exec nginx -g "daemon off;"