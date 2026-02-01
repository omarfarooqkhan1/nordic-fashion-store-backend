#!/bin/bash

# NordFlex - Docker Quick Start Script

echo "🚀 Starting NordFlex Docker Services..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Start Docker services
echo "📦 Starting MySQL and Mailpit containers..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Check if MySQL configuration is enabled for development
if grep -q "^DB_DATABASE=u672825292_nordflex" .env; then
    echo "✅ Using production database credentials for Docker MySQL"
else
    echo "🔄 Updating database configuration for Docker MySQL..."
    sed -i.bak 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i.bak 's/^DB_PORT=.*/DB_PORT=3306/' .env
    sed -i.bak 's/^DB_DATABASE=.*/DB_DATABASE=u672825292_nordflex/' .env
    sed -i.bak 's/^DB_USERNAME=.*/DB_USERNAME=u672825292_admin/' .env
    sed -i.bak 's/^DB_PASSWORD=.*/DB_PASSWORD=EftRQgW~2/' .env
    rm .env.bak
    echo "✅ Database configuration updated"
fi

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --seed

echo "✅ Docker services started successfully!"
echo ""
echo "📊 Services:"
echo "   - Application: http://localhost:8000"
echo "   - Mailpit Web UI: http://localhost:8025"
echo "   - MySQL: localhost:3306"
echo ""
echo "🔐 Database Connection:"
echo "   - Host: 127.0.0.1"
echo "   - Port: 3306"
echo "   - Database: u672825292_nordflex"
echo "   - Username: u672825292_admin"
echo "   - Password: EftRQgW~2"
echo ""
echo "🚀 Start Laravel development server:"
echo "   php artisan serve"