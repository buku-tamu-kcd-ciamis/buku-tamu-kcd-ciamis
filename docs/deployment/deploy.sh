#!/bin/bash

# ========================================
# Deployment Script for Production Server
# ========================================

set -e  # Exit on error

echo "🚀 Starting deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration (akan dioverride oleh GitHub Actions)
PROJECT_PATH="${PROJECT_PATH:-/var/www/cadisdik/public_html}"
BRANCH="${BRANCH:-main}"

echo -e "${YELLOW}📂 Project Path: ${PROJECT_PATH}${NC}"
echo -e "${YELLOW}🌿 Branch: ${BRANCH}${NC}"

# Navigate to project directory
cd "$PROJECT_PATH" || exit 1

# Enable maintenance mode
echo "⏸️  Enabling maintenance mode..."
php artisan down || true

# Pull latest code
echo "📥 Pulling latest code from Git..."
git fetch origin
git reset --hard "origin/${BRANCH}"
git pull origin "$BRANCH"

# Install/update Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Clear old caches
echo "🧹 Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Cache configuration
echo "💾 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# Set proper permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Restart queue workers (if using queues)
echo "🔄 Restarting queue workers..."
php artisan queue:restart || true

# Disable maintenance mode
echo "▶️  Disabling maintenance mode..."
php artisan up

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}🎉 Application is now live!${NC}"
