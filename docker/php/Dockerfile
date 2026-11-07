FROM php:8.4-fpm

# Dependències del sistema i extensions de PHP que necessita Laravel
RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip gd bcmath \
    && rm -rf /var/lib/apt/lists/*

# Pujada de fotos: fins a 10 MB per fitxer (per defecte PHP en permet 2)
RUN printf "upload_max_filesize=10M\npost_max_size=12M\n" > "$PHP_INI_DIR/conf.d/uploads.ini"

# Rendiment a Windows: el codi és en una carpeta compartida i llegir-ne els fitxers és lent.
# OPcache guarda el PHP compilat i només revisa si ha canviat cada 2 s; la caché de rutes
# evita resoldre els mateixos camins de vendor/ a cada petició.
RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=20000\nopcache.validate_timestamps=1\nopcache.revalidate_freq=2\nrealpath_cache_size=4096K\nrealpath_cache_ttl=600\n" > "$PHP_INI_DIR/conf.d/opcache-dev.ini"

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer

# Tinker (PsySH) desa la configuració i l'historial a /tmp
# www-data no pot escriure a /var/www
ENV XDG_CONFIG_HOME=/tmp/.config

# L'usuari del contenidor té el mateix UID/GID que l'usuari de l'ordinador
# (evita problemes de permisos amb els fitxers creats dins del contenidor)
ARG UID=1000
ARG GID=1000
RUN groupmod -o -g ${GID} www-data && usermod -o -u ${UID} -g www-data www-data

WORKDIR /var/www/html
USER www-data
