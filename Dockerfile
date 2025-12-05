FROM php:8.1-apache

RUN docker-php-ext-install pdo_mysql && \
    a2enmod rewrite

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

EXPOSE 80
