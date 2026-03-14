#!/bin/bash

# Variables d'environnement par défaut
export APP_ENV=prod
export APP_SECRET=change_me_in_production
export JWT_SECRET_KEY=change_me_in_production

# Créer le répertoire pour le socket
mkdir -p /var/run

# Démarrer PHP-FPM en arrière-plan
php-fpm &

# Attendre que PHP-FPM soit prêt
sleep 2

# Démarrer Nginx au premier plan
nginx -g "daemon off;"
