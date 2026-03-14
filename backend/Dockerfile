FROM php:8.2-fpm-alpine

# Nettoyer le cache APK pour éviter les warnings
RUN rm -rf /var/cache/apk/* && \
    apk update --no-cache && \
    apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    nginx \
    supervisor \
    libzip-dev \
    icu-dev \
    icu-libs

RUN docker-php-ext-install \
    pdo_pgsql \
    gd \
    opcache \
    zip \
    bcmath \
    intl \
    ctype \
    mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ✅ Contexte = racine du repo, donc préfixer par backend/
COPY backend/composer.json backend/composer.lock ./

# Utiliser composer install directement pour éviter les problèmes de cache
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --no-dev \
    --verbose

# ✅ Copier tout le code backend
COPY backend/ .

# Créer les répertoires nécessaires avec les bonnes permissions
RUN mkdir -p var/cache/prod var/logs bootstrap/cache && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 var bootstrap/cache

# Créer le cache manuellement avec les bonnes permissions
RUN mkdir -p var/cache/prod/twig var/cache/prod/annotations && \
    chown -R www-data:www-data var/cache/prod && \
    touch var/cache/prod/.gitkeep

# Configuration PHP-FPM pour utiliser le socket
RUN echo '[www]\n\
user = www-data\n\
group = www-data\n\
listen = /var/run/php-fpm.sock\n\
listen.owner = www-data\n\
listen.group = www-data\n\
listen.mode = 0666\n\
pm = dynamic\n\
pm.max_children = 5\n\
pm.start_servers = 2\n\
pm.min_spare_servers = 1\n\
pm.max_spare_servers = 3' > /usr/local/etc/php-fpm.d/www.conf

# Configuration Nginx
RUN echo 'server {\n\
    listen 8000;\n\
    server_name localhost;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        fastcgi_pass unix:/var/run/php-fpm.sock;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
\n\
    location ~ /\.ht {\n\
        deny all;\n\
    }\n\
}' > /etc/nginx/conf.d/default.conf

# Script de démarrage pour Nginx + PHP-FPM
RUN echo '#!/bin/bash\n\
# Variables d\'environnement par défaut\n\
export APP_ENV=prod\n\
export APP_SECRET=change_me_in_production\n\
export JWT_SECRET_KEY=change_me_in_production\n\
\n\
# Créer le répertoire pour le socket\n\
mkdir -p /var/run\n\
\n\
# Démarrer PHP-FPM en arrière-plan\n\
php-fpm &\n\
\n\
# Attendre que PHP-FPM soit prêt\n\
sleep 2\n\
\n\
# Démarrer Nginx au premier plan\n\
nginx -g "daemon off;"' > /start.sh && \
    chmod +x /start.sh

# Revenir à root pour Nginx
USER root

EXPOSE 8000

CMD ["/start.sh"]