# Multi-stage build for Symfony application
FROM php:8.2-fpm-alpine as base

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
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pdo_mysql \
    gd \
    opcache \
    zip \
    exif \
    pcntl \
    bcmath \
    intl \
    xml \
    ctype

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# --- PRODUCTION STAGE ---
FROM base as production

# Install production dependencies
RUN apk add --no-cache supervisor

# 1. Copie des fichiers de configuration depuis la racine globale
# (On suppose ici que le dossier 'docker' est à la racine du projet)
COPY docker/php/php.ini-production /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 2. Copie du code source depuis le dossier backend
# On copie le CONTENU de backend dans le dossier de travail actuel (.)
COPY backend/ .

# 3. Installation des dépendances PHP pour la production
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts

# 4. Correction des permissions (Crucial pour bin/console et les dossiers var)
# On s'assure que bin/console est exécutable et que www-data possède les fichiers
RUN chmod +x bin/console && \
    chown -R www-data:www-data /var/www/html && \
    mkdir -p var bootstrap/cache && \
    chmod -R 775 var bootstrap/cache

# 5. Nettoyage et préchauffage du cache
# Note: On utilise APP_ENV=prod (format standard Symfony)
RUN APP_ENV=prod php bin/console cache:clear --no-warmup && \
    APP_ENV=prod php bin/console cache:warmup

# Expose port for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]