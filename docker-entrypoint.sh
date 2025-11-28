#!/bin/bash
set -e

echo "🚀 Starting Laravel Application..."

# Wait for database to be ready
echo "⏳ Waiting for database..."
until php artisan db:show 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "✅ Database is ready!"

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force

# Clear and cache configuration
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "📦 Caching configuration..."
php artisan config:cache

# Create storage link if not exists
if [ ! -L /var/www/public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Set correct permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "🎉 Application is ready!"

# Execute the main command
exec "$@"

