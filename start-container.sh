#!/bin/bash
# Arranque en Railway (Railpack usa este archivo en lugar del suyo; ver docs/DEPLOY.md).
# Igual que el de Railpack para Laravel, pero NUNCA migra: el esquema se carga a mano con database/schema.sql.
set -e

# El volumen se monta vacío en /app/storage la primera vez: crea las carpetas que Laravel necesita.
mkdir -p storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

php artisan optimize:clear
php artisan optimize

exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1
