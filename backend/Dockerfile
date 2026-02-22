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
    php8-cli

# Install PHP extensions
RUN docker-php-ext-install \
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
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/html

# Development stage
FROM base as development

# Install development dependencies
RUN apk add --no-cache \
    supervisor \
    nginx

# Copy configuration files
COPY docker/php/php.ini-development /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/nginx-dev.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

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
COPY docker/php/php.ini-production /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application files
COPY . .

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
