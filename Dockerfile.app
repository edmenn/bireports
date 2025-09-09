# -------- Stage 1: builder (Composer) --------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress \
    && rm -rf /root/.composer
COPY . .
# Opcional: compilar clases para prod
RUN php artisan vendor:publish --all --force || true

# -------- Stage 2: runtime (PHP-FPM Alpine) --------
FROM php:8.3-fpm-alpine

# Paquetes mínimos + extensiones requeridas
RUN set -eux; \
    apk add --no-cache bash fcgi file libpq-dev oniguruma-dev icu-dev tzdata; \
    docker-php-ext-configure intl; \
    docker-php-ext-install -j$(nproc) intl mbstring pdo pdo_pgsql opcache

# Opcional: ajustes de OPcache (rendimiento)
RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.enable_cli=0'; \
      echo 'opcache.jit=1255'; \
      echo 'opcache.jit_buffer_size=64M'; \
      echo 'opcache.memory_consumption=256'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Usuario no-root (uid/gid 1000)
RUN addgroup -g 1000 app && adduser -D -G app -u 1000 app
USER app

WORKDIR /var/www/html

# Copiamos el código y vendor desde el builder
# Usamos --chown=app:app para permisos correctos
COPY --from=vendor --chown=app:app /app /var/www/html

# Prepara bootstrap/cache (necesita write; lo manejará el volumen storage para runtime)
RUN mkdir -p bootstrap/cache storage/framework/{cache,views,sessions} && \
    chmod -R 775 bootstrap/cache storage

# Exponemos puerto interno de php-fpm
EXPOSE 9000

# Defendemos la inmutabilidad en runtime (compose pondrá read_only y tmpfs)
CMD ["php-fpm", "-F"]
