#!/bin/bash

# S'assurer que les répertoires de cache existent avec les bonnes permissions
mkdir -p var/cache/prod var/logs
chown -R www-data:www-data var
chmod -R 775 var

# Démarrer PHP-FPM
php-fpm
