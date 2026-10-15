FROM php:8.2-apache

# Rozszerzenia potrzebne do PDO (projekt używa PDO + MySQL).
RUN docker-php-ext-install pdo_mysql mysqli \
    && a2enmod rewrite

# msmtp jako lokalny MTA: PHP mail() -> /usr/sbin/sendmail -> relay SMTP.
# Dzięki temu wysyłka działa bez Composera i zewnętrznych bibliotek.
RUN apt-get update \
    && apt-get install -y --no-install-recommends msmtp msmtp-mta ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Własny VirtualHost: DocumentRoot na public/ + przekierowanie / -> /?page=home.
COPY docker/php/000-default.conf /etc/apache2/sites-available/000-default.conf

# Bezpieczna obsługa błędów: display_errors=Off, log do pliku poza web rootem.
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

# Entrypoint generuje /etc/msmtprc z .env przy starcie kontenera — hasło SMTP
# nie trafia do obrazu, a plik dostaje prawa 600 i właściciela www-data.
COPY docker/php/entrypoint.sh /usr/local/bin/owc-entrypoint.sh
RUN chmod +x /usr/local/bin/owc-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/owc-entrypoint.sh"]
CMD ["apache2-foreground"]
