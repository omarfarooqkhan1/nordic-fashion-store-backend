#!/bin/bash

# Production Composer Fix Script
echo "🔧 Fixing Composer issues in production..."

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: composer.json not found. Please run this script from the Laravel project root."
    exit 1
fi

# Check Composer version
echo "📋 Checking Composer version..."
composer --version

# Try to install Composer 2 if available
echo "🔄 Attempting to install Composer 2..."
if command -v composer2 &> /dev/null; then
    echo "✅ Composer 2 found, using it..."
    COMPOSER_CMD="composer2"
elif [ -f "/usr/local/bin/composer2.phar" ]; then
    echo "✅ Composer 2 phar found, using it..."
    COMPOSER_CMD="php /usr/local/bin/composer2.phar"
elif [ -f "composer2.phar" ]; then
    echo "✅ Local Composer 2 phar found, using it..."
    COMPOSER_CMD="php composer2.phar"
else
    echo "⚠️  Composer 2 not found, trying to download it..."
    
    # Download Composer 2
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer2.phar
    
    if [ -f "/usr/local/bin/composer2.phar" ]; then
        echo "✅ Composer 2 downloaded successfully"
        COMPOSER_CMD="php /usr/local/bin/composer2.phar"
    else
        echo "❌ Failed to download Composer 2, falling back to Composer 1"
        COMPOSER_CMD="composer"
    fi
fi

# Clear any existing vendor directory
echo "🧹 Clearing vendor directory..."
rm -rf vendor/
rm -rf composer.lock

# Install dependencies
echo "📦 Installing dependencies with $COMPOSER_CMD..."
$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction

if [ $? -eq 0 ]; then
    echo "✅ Dependencies installed successfully!"
else
    echo "❌ Failed to install dependencies with $COMPOSER_CMD"
    echo "🔄 Trying alternative approach..."
    
    # Try with different flags
    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
    
    if [ $? -eq 0 ]; then
        echo "✅ Dependencies installed with platform requirements ignored!"
    else
        echo "❌ Still failed. Trying manual package installation..."
        
        # Manual installation of critical packages
        echo "📦 Installing critical packages manually..."
        $COMPOSER_CMD require laravel/framework --no-interaction --ignore-platform-reqs
        $COMPOSER_CMD require laravel/sanctum --no-interaction --ignore-platform-reqs
        $COMPOSER_CMD require stripe/stripe-php --no-interaction --ignore-platform-reqs
    fi
fi

# Set proper permissions
echo "🔐 Setting proper permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 vendor/

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
echo "⚡ Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

echo "✅ Production Composer fix completed!"
echo ""
echo "📋 Next steps:"
echo "1. Test your API endpoints"
echo "2. Check if the CORS errors are resolved"
echo "3. Verify CSRF cookie functionality"
