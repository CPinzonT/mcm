#!/usr/bin/env sh
set -e

cd /var/www/html

role="${1:-web}"

if [ "$role" = "web" ] || [ "$role" = "worker" ] || [ "$role" = "scheduler" ]; then
    mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    chown -R www-data:www-data storage bootstrap/cache

    if [ -z "${APP_KEY:-}" ]; then
        echo "ERROR: APP_KEY no esta definido."
        echo "Genera una clave con: php artisan key:generate --show"
        exit 1
    fi
fi

if [ "$role" = "web" ]; then
    php artisan storage:link || true

    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        php artisan migrate --force
    fi

    if [ "${RUN_SEEDERS:-true}" = "true" ]; then
        php artisan db:seed --force
    fi

    if [ "${CACHE_CONFIG:-true}" = "true" ]; then
        php artisan config:cache
    fi
fi

case "$role" in
    web)
        php-fpm -D
        exec nginx -g "daemon off;"
        ;;
    worker)
        exec php artisan queue:work \
            --sleep="${QUEUE_WORKER_SLEEP:-3}" \
            --tries="${QUEUE_WORKER_TRIES:-3}" \
            --timeout="${QUEUE_WORKER_TIMEOUT:-90}" \
            --max-time="${QUEUE_WORKER_MAX_TIME:-3600}"
        ;;
    scheduler)
        exec php artisan schedule:work
        ;;
    *)
        exec "$@"
        ;;
esac
