FROM php:8.0-apache

COPY . .

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    a2enmod headers && \
    a2enmod rewrite

RUN cp /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/ && \
    cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/

EXPOSE 80