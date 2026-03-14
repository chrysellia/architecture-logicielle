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

# Copier les fichiers de configuration
COPY backend/nginx.conf /etc/nginx/http.d/default.conf
COPY backend/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY backend/start.sh /start.sh
RUN chmod +x /start.sh

# Créer les répertoires nécessaires avec les bonnes permissions
RUN mkdir -p var/cache/prod var/logs bootstrap/cache && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 var bootstrap/cache

# Créer le cache manuellement avec les bonnes permissions
RUN mkdir -p var/cache/prod/twig var/cache/prod/annotations && \
    chown -R www-data:www-data var/cache/prod && \
    touch var/cache/prod/.gitkeep

# Revenir à root pour Nginx
USER root

EXPOSE 8000

CMD ["sh", "/start.sh"]