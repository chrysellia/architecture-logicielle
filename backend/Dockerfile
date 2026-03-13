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

# Donner les permissions avant les commandes Symfony
RUN chown -R www-data:www-data /var/www/html && \
    mkdir -p var bootstrap/cache && \
    chmod -R 775 var bootstrap/cache

# Exécuter les commandes Symfony avec l'utilisateur www-data
USER www-data
RUN APP_ENV=prod APP_SECRET=change_me JWT_SECRET_KEY=change_me php bin/console cache:clear --no-warmup --no-interaction || true
RUN APP_ENV=prod APP_SECRET=change_me JWT_SECRET_KEY=change_me php bin/console cache:warmup --no-interaction || true

# Revenir à root pour le nettoyage final
USER root
RUN rm -rf var/cache/*

EXPOSE 8000

CMD ["php-fpm"]