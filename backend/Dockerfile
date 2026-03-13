FROM php:8.2-fpm-alpine

# Install system dependencies
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

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    gd \
    opcache \
    zip \
    bcmath \
    intl \
    ctype \
    mbstring

# Install Composer (via image officielle, plus fiable que curl)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copier UNIQUEMENT les fichiers Composer en premier (cache Docker)
COPY composer.json ./

# Install dependencies
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --no-dev \
    --no-cache \
    --verbose

# Copier le reste du code APRÈS composer install
COPY . .

# Permissions et cache Symfony
RUN chown -R www-data:www-data /var/www/html && \
    mkdir -p var bootstrap/cache && \
    chmod -R 775 var bootstrap/cache && \
    APP_ENV=prod php bin/console cache:clear --no-warmup && \
    APP_ENV=prod php bin/console cache:warmup

EXPOSE 8000

CMD ["php-fpm"]