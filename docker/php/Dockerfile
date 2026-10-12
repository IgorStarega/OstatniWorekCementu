FROM php:8.2-apache

# Rozszerzenia potrzebne do PDO (projekt używa PDO + MySQL).
RUN docker-php-ext-install pdo_mysql mysqli \
    && a2enmod rewrite

# Własny VirtualHost: DocumentRoot na public/ + przekierowanie / -> /?page=home.
COPY docker/php/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
