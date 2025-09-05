#!/bin/bash

# Production Deployment Script for Nordic Skin
echo "🚀 Starting production deployment..."

# Set production environment
echo "📝 Setting production environment..."
cp env.production .env

# Generate application key if not set
echo "🔑 Generating application key..."
php artisan key:generate

# Clear and cache configuration
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configuration for production
echo "⚡ Caching configuration for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed database if needed
echo "🌱 Seeding database..."
php artisan db:seed --force

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Clear OPcache if available
echo "🧽 Clearing OPcache..."
php artisan optimize:clear

echo "✅ Production deployment completed!"
echo ""
echo "📋 Next steps:"
echo "1. Update your domain in the .env file"
echo "2. Update CORS origins in config/cors.php"
echo "3. Test the API endpoints"
echo "4. Deploy the frontend with the updated .htaccess"
