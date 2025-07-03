FROM php:8.4-fpm

RUN apt-get update && apt-get install -y git zip unzip

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
  && (echo 'memory_limit = 1024M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini) \
  &&  (echo 'clear_env = no' >> /usr/local/etc/php/conf.d/env-in-workers.ini)

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
