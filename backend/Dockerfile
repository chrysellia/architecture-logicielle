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
    && docker-php-ext-install \
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
    ctype \
    iconv \
    mbstring

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Development stage
FROM base as development

# Install development dependencies
RUN apk add --no-cache \
    supervisor \
    nginx

# Copy source code
COPY **backend/** /var/www/html

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy configuration files
COPY backend/docker/php/php.ini-development /usr/local/etc/php/php.ini
COPY backend/docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY backend/docker/nginx/nginx-dev.conf /etc/nginx/nginx.conf
COPY backend/docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose ports
EXPOSE 8000

# Start services
CMD sh -c "php -S 0.0.0.0:8000 -t public/"

# Production stage
FROM base as production

# Install production dependencies
RUN apk add --no-cache \
    supervisor

# Copy configuration files
COPY backend/docker/php/php.ini-production /usr/local/etc/php/php.ini
COPY backend/docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY backend/docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application files
COPY **backend/** .

# Install PHP dependencies for production
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts

# Clear and warm up cache
RUN APP_ENV=production composer run-script post-install-cmd \
    && APP_ENV=production php bin/console cache:clear \
    && APP_ENV=production php bin/console cache:warmup

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/var \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
