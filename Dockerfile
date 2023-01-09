FROM composer:2.5 as vendor

WORKDIR /tmp/

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:8-cli as app

COPY . /var/www/html
COPY --from=vendor /tmp/vendor/ /var/www/html/vendor/

ENV PORT=10000
EXPOSE 10000
CMD [ "/var/www/html/bin/start.sh" ]