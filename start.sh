#!/bin/sh
set -e

# Run migrations
php artisan migrate --force

# Create storage link if it doesn't exist
php artisan storage:link || true

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the server
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

