FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libonig-dev \
        libcurl4-openssl-dev \
    && docker-php-ext-install -j$(nproc) mbstring \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

COPY app ./app
COPY config ./config
COPY public ./public
COPY index.php ./index.php

RUN mkdir -p public/uploads/inventario public/uploads/dar-baja \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fsS http://127.0.0.1/login >/dev/null || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
