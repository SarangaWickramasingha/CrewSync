FROM php:8.2-apache

# PHP extensions needed by the app (pdo_mysql for DB, mysqli fallback)
RUN docker-php-ext-install pdo_mysql mysqli \
    && a2enmod rewrite

# Apache + PHP runtime configuration
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-crewsync.ini

EXPOSE 80