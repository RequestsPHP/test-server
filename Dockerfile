FROM php:8.0-apache

COPY . .

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    a2enmod headers && \
    a2enmod rewrite

EXPOSE 80