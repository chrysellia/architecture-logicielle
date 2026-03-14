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

COPY backend/composer.json backend/composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --no-dev

COPY backend/ .

COPY backend/nginx.conf /etc/nginx/http.d/default.conf
COPY backend/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY backend/start.sh /start.sh
RUN chmod +x /start.sh

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV APP_SECRET=change_me_in_production

RUN mkdir -p var/cache/prod var/logs bootstrap/cache && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 var bootstrap/cache && \
    php bin/console cache:clear --env=prod --no-warmup && \
    php bin/console cache:warmup --env=prod && \
    chown -R www-data:www-data var/cache

EXPOSE 8000

CMD ["sh", "/start.sh"]