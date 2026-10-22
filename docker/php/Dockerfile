FROM php:8.2-apache

# Rozszerzenia PDO (projekt używa PDO + MySQL) i mod_rewrite dla VirtualHosta.
RUN docker-php-ext-install pdo_mysql mysqli \
    && a2enmod rewrite

# VirtualHost: DocumentRoot na public/ + przekierowanie / -> /?page=home.
COPY docker/php/000-default.conf /etc/apache2/sites-available/000-default.conf

# Bezpieczna obsługa błędów: display_errors=Off, log do pliku poza web rootem.
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

WORKDIR /var/www/html

CMD ["apache2-foreground"]
