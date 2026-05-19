FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    mysql-client \
    oniguruma-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install bcmath intl pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 8000

CMD ["sh", "-lc", "composer install --no-interaction && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000"]
