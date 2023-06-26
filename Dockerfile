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
    a2enmod rewrite && \
    a2enmod session && \
    a2enmod session_cookie && \
    a2enmod session_crypto && \
    a2enmod ssl

EXPOSE 80
EXPOSE 443
