#!/bin/bash
# Arranque en Render (ver docs/DEPLOY.md). NUNCA migra: el esquema se carga a mano con database/schema.sql.
set -e

# Render dice en qué puerto escuchar; Caddy sirve HTTP plano (Render pone el HTTPS delante).
export SERVER_NAME=":${PORT:-10000}"

# Aiven (y otros MySQL gestionados) exigen TLS: el certificado de la CA llega como variable.
if [ -n "$DB_CA_CERT" ]; then
    printf '%s\n' "$DB_CA_CERT" > /tmp/db-ca.pem
    export MYSQL_ATTR_SSL_CA=/tmp/db-ca.pem
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

php artisan optimize:clear
php artisan optimize

CADDYFILE=/etc/frankenphp/Caddyfile
[ -f "$CADDYFILE" ] || CADDYFILE=/etc/caddy/Caddyfile
exec frankenphp run --config "$CADDYFILE" --adapter caddyfile
