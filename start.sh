#!/bin/sh
set -e

# Wait a bit for database to be ready
sleep 2

# Run migrations (with retry logic)
php artisan migrate --force || {
    echo "Migration failed, waiting 5 seconds and retrying..."
    sleep 5
    php artisan migrate --force
}

# Create storage link if it doesn't exist
php artisan storage:link || true

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the server
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

