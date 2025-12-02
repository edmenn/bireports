# -------- Stage 1: builder (PHP 8.3 + Composer + vendors) --------
# Usamos PHP 8.3 CLI Alpine para que coincida con el runtime
FROM php:8.3-cli-alpine AS vendor
WORKDIR /app

# 0) Paquetes necesarios para extensiones y para Composer
RUN set -eux; \
    apk add --no-cache \
      git \
      unzip \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev \
      libwebp-dev \
      libxpm-dev

# 1) Instalamos extensión GD (requerida por phpspreadsheet)
RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm; \
    docker-php-ext-install -j"$(nproc)" gd; \
    php -m | grep -qi gd

# 2) Instalamos Composer de forma global
RUN set -eux; \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
    rm composer-setup.php; \
    composer --version

# 3) Copiamos solo composer.* para cachear mejor
COPY composer.json composer.lock ./

# 4) Instalamos vendors sin scripts (aún no existe artisan)
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

# 5) Ahora sí copiamos el código fuente completo
COPY . .

# 6) Generamos autoload optimizado y ejecutamos scripts (artisan ya existe)
#    package:discover puede fallar en algunos entornos; no consideres fatal ese caso
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true


# -------- Stage 2: runtime (PHP-FPM Alpine endurecido) --------
FROM php:8.3-fpm-alpine

# Extensiones necesarias (ajusta según tu app)
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
    php -r 'if(!function_exists("mb_split")){fwrite(STDERR, "mb_split missing\n"); exit(1);}'

# Opcional: OPcache para producción (ya viene incluido en la imagen)
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

# Copiamos aplicación y vendors desde el stage builder con permisos correctos
COPY --from=vendor --chown=app:app /app /var/www/html

# Aseguramos rutas de escritura (en runtime tu compose monta volumen para storage)
RUN mkdir -p bootstrap/cache storage/framework/{cache,views,sessions} \
 && chmod -R 775 bootstrap/cache storage

# Copiar logos/recursos por idioma desde resources/lang/<locale> a public/lang/<locale>
RUN set -eux; \
    mkdir -p public/lang; \
    for d in resources/lang/*; do \
      if [ -d "$d" ]; then \
        loc="$(basename "$d")"; \
        mkdir -p "public/lang/$loc"; \
        find "$d" -maxdepth 1 -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.svg' -o -iname '*.webp' \) -exec cp -a {} "public/lang/$loc/" \; \
      fi; \
    done

EXPOSE 9000
CMD ["php-fpm", "-F"]
