#!/usr/bin/env bash
# Perbarui aplikasi di VPS dari branch main. Jalankan dari folder aplikasi:
#   bash deploy/deploy.sh
set -euo pipefail

php artisan down --retry=15 || true

git pull --ff-only origin main
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

php artisan migrate --force
php artisan optimize

php artisan up
echo "Selesai: $(git log -1 --format='%h %s')"
