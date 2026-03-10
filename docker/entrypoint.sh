#!/usr/bin/env sh
set -eu

cd /var/www/html

# Normalize common DB env names from PaaS dashboards.
if [ -z "${DB_DATABASE:-}" ] && [ -n "${DB_NAME:-}" ]; then
  export DB_DATABASE="${DB_NAME}"
fi

if [ -z "${DB_USERNAME:-}" ] && [ -n "${DB_USER:-}" ]; then
  export DB_USERNAME="${DB_USER}"
fi

if [ -z "${DB_PASSWORD:-}" ] && [ -n "${DB_PASS:-}" ]; then
  export DB_PASSWORD="${DB_PASS}"
fi

if [ -z "${DB_CONNECTION:-}" ]; then
  if [ -n "${DB_URL:-}" ] || [ -n "${DB_HOST:-}" ] || [ -n "${DB_DATABASE:-}" ]; then
    export DB_CONNECTION="pgsql"
  else
    export DB_CONNECTION="sqlite"
  fi
fi

if [ "${DB_CONNECTION}" = "sqlite" ]; then
  mkdir -p database
  touch database/database.sqlite
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
