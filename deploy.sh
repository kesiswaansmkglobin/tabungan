#!/bin/bash
# Deployment script for Tabungan Siswa SMK Globin
# Usage: bash deploy.sh

set -e

echo "=== Deploying Tabungan Siswa ==="

# 0. Pre-flight checks
if [ ! -f .env ]; then
    echo "ERROR: .env file not found. Copy .env.example to .env and configure it first."
    exit 1
fi

if grep -q "DB_PASSWORD=\"\?$\"\|DB_USERNAME=root" .env; then
    echo "WARNING: DB_USERNAME=root or DB_PASSWORD is empty. Consider using a dedicated DB user with a strong password."
fi

# 1. Pull latest code (if using git)
if [ -d .git ]; then
    git pull origin main
fi

# 2. Install PHP dependencies (no dev in production)
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# 3. Install and build frontend assets
echo "Building frontend assets..."
npm ci
npm run build

# 4. Run migrations
echo "Running database migrations..."
php artisan migrate --force

# 5. Clear cache before re-caching
echo "Clearing old cache..."
php artisan optimize:clear

# 6. Optimize Laravel
echo "Optimizing Laravel..."
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Create storage link
php artisan storage:link --force

# 8. Fix storage permissions
echo "Setting storage permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 9. Restart queue worker & reverb
echo "Restarting queue worker..."
php artisan queue:restart

echo "=== Deployment complete ==="
