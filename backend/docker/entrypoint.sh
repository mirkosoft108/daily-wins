#!/bin/sh
set -eu

# Artisan commands can be run with docker run ... php artisan ...
if [ "${1:-}" != "apache2-foreground" ]; then
    exec "$@"
fi

: "${APP_KEY:?Set APP_KEY before starting the API}"
: "${FRONTEND_ORIGINS:?Set FRONTEND_ORIGINS before starting the API}"
if [ "${DB_CONNECTION:-}" != "pgsql" ]; then
    echo 'Production requires DB_CONNECTION=pgsql.' >&2
    exit 1
fi

PORT="${PORT:-10000}"
case "$PORT" in
    ''|*[!0-9]*) echo 'PORT must be an integer.' >&2; exit 1 ;;
esac
if [ "$PORT" -lt 1 ] || [ "$PORT" -gt 65535 ]; then
    echo 'PORT must be between 1 and 65535.' >&2
    exit 1
fi

printf 'Listen %s\n' "$PORT" > /etc/apache2/ports.conf
sed "s/__PORT__/$PORT/g" /usr/local/etc/daily-wins/apache.conf > /etc/apache2/sites-available/000-default.conf

# Cache runtime settings only; credentials never enter the image build.
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction

if [ "${RUN_MIGRATIONS:-false}" = 'true' ]; then
    php artisan migrate --force --no-interaction
fi
if [ "${SEED_DEMO:-false}" = 'true' ]; then
    php artisan db:seed --force --no-interaction
fi

exec "$@"
