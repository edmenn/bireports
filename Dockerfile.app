# -----------------------------------------------------------
#  Stage 1: builder (PHP 8.3 CLI + Composer + vendors)
# -----------------------------------------------------------
FROM php:8.3-cli-alpine AS vendor
WORKDIR /app

# Paquetes necesarios para extensiones y Composer
RUN set -eux; \
    apk add --no-cache \
      git \
      unzip \
      libzip-dev \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev \
      libwebp-dev \
      libxpm-dev

# Extensiones requeridas (GD + ZIP)
RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm; \
    docker-php-ext-install -j"$(nproc)" gd zip; \
    php -m | grep -qi gd; \
    php -m | grep -qi zip

# Instalar Composer
RUN set -eux; \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
    rm composer-setup.php; \
    composer --version

# Copiamos composer.* primero para usar cache
COPY composer.json composer.lock ./

# Instalar dependencias sin scripts
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

# Copiar el código fuente completo
COPY . .

# Never reuse package manifests generated with development-only dependencies.
# Discovery is part of the build gate: a broken provider must fail the image.
RUN rm -f bootstrap/cache/*.php \
 && composer dump-autoload -o \
 && php artisan package:discover --ansi



# -----------------------------------------------------------
#  Stage 2: runtime (PHP 8.3 FPM Alpine)
# -----------------------------------------------------------
FROM php:8.3-fpm-alpine

# Extensiones necesarias
RUN set -eux; \
    apk add --no-cache \
      bash \
      fcgi \
      file \
      icu-dev \
      tzdata \
      libpq-dev \
      oniguruma \
      oniguruma-dev \
      ca-certificates \
      libzip-dev \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev \
      libwebp-dev \
      libxpm-dev; \
    docker-php-ext-configure intl; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm; \
    docker-php-ext-install -j"$(nproc)" gd intl mbstring pdo pdo_pgsql zip; \
    php -m | grep -qi gd; \
    php -m | grep -qi mbstring; \
    php -m | grep -qi zip; \
    php -r 'if(!function_exists("mb_split")){fwrite(STDERR, "mb_split missing\n"); exit(1);}'

# OPcache optimizado para producción
RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.enable_cli=0'; \
      echo 'opcache.jit=1255'; \
      echo 'opcache.jit_buffer_size=64M'; \
      echo 'opcache.memory_consumption=256'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Usuario no root
RUN addgroup -g 1000 app && adduser -D -G app -u 1000 app
USER app

WORKDIR /var/www/html

# Copiar aplicación + vendors desde el build
COPY --from=vendor --chown=app:app /app /var/www/html

# Permisos de runtime
RUN mkdir -p bootstrap/cache storage/framework/{cache,views,sessions} \
 && chmod -R 775 bootstrap/cache storage

# (Simplificado) Creamos la carpeta de idiomas pública
RUN mkdir -p public/lang

EXPOSE 9000
CMD ["php-fpm", "-F"]
