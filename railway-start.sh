#!/bin/bash

# Railway Laravel startup script
echo "Starting Laravel app on Railway..."

# Set proper permissions for Laravel
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Clear and cache Laravel configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate app key if missing
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php artisan key:generate --force
fi

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link

# Start the web server
php artisan serve --host=0.0.0.0 --port=$PORT