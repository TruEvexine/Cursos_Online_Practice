# Imagen para Render (ver docs/DEPLOY.md). Solo se usa en producción; el desarrollo sigue sin Docker.
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM dunglas/frankenphp:1-php8.4
RUN apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions gd intl pdo_mysql mbstring fileinfo opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Subidas de hasta 10 MB (la app valida 10 MB); el php.ini de producción trae 2 MB.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize=10M\npost_max_size=12M\nmemory_limit=256M\n' > "$PHP_INI_DIR/conf.d/zz-app.ini"

RUN chmod +x start-container.sh
ENTRYPOINT ["./start-container.sh"]
