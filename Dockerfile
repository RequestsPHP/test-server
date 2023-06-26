FROM php:8.0-apache

COPY . .

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev

RUN a2enmod headers && \
    a2enmod rewrite && \
    service apache2 restart

EXPOSE 80