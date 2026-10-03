FROM php:8.4-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && docker-php-ext-install -j2 pdo_pgsql opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=pgsql \
    DB_SSLMODE=require \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info \
    SESSION_DRIVER=array \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync \
    PORT=10000 \
    RUN_MIGRATIONS=false \
    SEED_DEMO=false

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader

COPY . .
RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --no-dev --optimize --no-scripts \
    && mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/php.ini /usr/local/etc/php/conf.d/daily-wins.ini
COPY docker/apache.conf /usr/local/etc/daily-wins/apache.conf
COPY docker/entrypoint.sh /usr/local/bin/daily-wins-entrypoint
RUN chmod +x /usr/local/bin/daily-wins-entrypoint

EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/daily-wins-entrypoint"]
CMD ["apache2-foreground"]
