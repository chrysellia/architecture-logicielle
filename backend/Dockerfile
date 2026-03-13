FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
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

RUN composer update --lock --no-scripts --no-interaction

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --no-dev \
    --verbose

# ✅ Copier tout le code backend
COPY backend/ .

RUN chown -R www-data:www-data /var/www/html && \
    mkdir -p var bootstrap/cache && \
    chmod -R 775 var bootstrap/cache && \
    APP_ENV=prod php bin/console cache:clear --no-warmup && \
    APP_ENV=prod php bin/console cache:warmup

EXPOSE 8000

CMD ["php-fpm"]