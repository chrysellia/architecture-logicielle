FROM php:8.2-fpm-alpine

RUN rm -rf /var/cache/apk/* && \
    apk update --no-cache && \
    apk add --no-cache \
    git curl libpng-dev oniguruma-dev libxml2-dev \
    zip unzip postgresql-dev nginx supervisor \
    libzip-dev icu-dev icu-libs su-exec

RUN docker-php-ext-install \
    pdo_pgsql gd opcache zip bcmath intl ctype mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --no-dev

COPY . .

# Configs depuis les bons chemins
COPY docker/nginx/nginx-dev.conf /etc/nginx/nginx.conf
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Créer les dossiers nécessaires à supervisor
RUN mkdir -p /var/log/supervisor /var/run

ENV APP_ENV=prod
ENV APP_DEBUG=0

# Warmup du cache Symfony
RUN php bin/console cache:warmup --env=prod --no-debug || true

RUN mkdir -p var/cache/prod var/logs && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 var var/logs

EXPOSE 10000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]