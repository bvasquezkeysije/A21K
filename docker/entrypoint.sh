#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
