#!/bin/sh
set -e
cd /var/www/html
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction --optimize-autoloader
fi
exec "$@"
