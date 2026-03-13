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
    libzip-dev

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

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy source code
COPY . /var/www/html

# Install dependencies with verbose output for debugging
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-dev --verbose

# Set permissions and run Symfony commands manually
RUN chown -R www-data:www-data /var/www/html && \
    mkdir -p var bootstrap/cache && \
    chmod -R 775 var bootstrap/cache && \
    APP_ENV=prod php bin/console cache:clear --no-warmup && \
    APP_ENV=prod php bin/console cache:warmup

# Expose ports
EXPOSE 8000

# Start PHP-FPM
CMD ["php-fpm"]
