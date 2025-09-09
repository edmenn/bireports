# -------- Stage 1: builder (Composer + vendors) --------
FROM composer:2 AS vendor
WORKDIR /app

# 1) Copiamos sólo composer.* para cachear mejor
COPY composer.json composer.lock ./

# 2) Instalamos vendors sin scripts (aún no existe artisan)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# 3) Ahora sí copiamos el código fuente completo
COPY . .

# 4) Generamos autoload optimizado y ejecutamos scripts (artisan ya existe)
#    package:discover puede fallar en algunos entornos; no consideres fatal ese caso
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# -------- Stage 2: runtime (PHP-FPM Alpine endurecido) --------
FROM php:8.3-fpm-alpine

# Extensiones necesarias (ajusta según tu app)
RUN set -eux; \
    apk add --no-cache bash fcgi file icu-dev tzdata libpq-dev oniguruma oniguruma-dev; \
    docker-php-ext-configure intl; \
    docker-php-ext-install -j"$(nproc)" intl mbstring pdo pdo_pgsql opcache; \
    php -m | grep -qi mbstring; \
    php -r 'if(!function_exists("mb_split")){fwrite(STDERR, "mb_split missing\n"); exit(1);}';

# Opcional: OPcache para prod
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
# para que sean servidos por Nginx sin pasos extra.
RUN set -eux; \
    mkdir -p public/lang; \
    for d in resources/lang/*; do \
      if [ -d "$d" ]; then \
        loc="$(basename "$d")"; \
        mkdir -p "public/lang/$loc"; \
        find "$d" -maxdepth 1 -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.svg' -o -iname '*.webp' \) -exec cp -a {} "public/lang/$loc/" \; \
      ; fi; \
    done

EXPOSE 9000
CMD ["php-fpm", "-F"]
