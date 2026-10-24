FROM php:8.4-fpm

# Dependències del sistema i extensions de PHP que necessita Laravel
RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip gd bcmath \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer

# L'usuari del contenidor té el mateix UID/GID que l'usuari de l'ordinador
# (evita problemes de permisos amb els fitxers creats dins del contenidor)
ARG UID=1000
ARG GID=1000
RUN groupmod -o -g ${GID} www-data && usermod -o -u ${UID} -g www-data www-data

WORKDIR /var/www/html
USER www-data
