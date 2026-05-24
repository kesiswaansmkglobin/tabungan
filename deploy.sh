#!/bin/bash
# Deployment script for Tabungan Siswa SMK Globin
# Usage: bash deploy.sh

set -e

echo "=== Deploying Tabungan Siswa ==="

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

# 5. Clear and cache config, routes, views, events
echo "Optimizing Laravel..."
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Create storage link
php artisan storage:link --force

# 7. Restart queue worker
echo "Restarting queue worker..."
php artisan queue:restart

echo "=== Deployment complete ==="
