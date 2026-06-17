#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

echo "Ensuring private storage directories..."
php artisan storage:ensure-private-dirs

echo "Fixing ownership and permissions for storage..."
chown -R www-data:www-data storage/app/private storage/logs storage/framework 2>/dev/null || true
chmod -R ug+rwX storage/app/private storage/logs storage/framework 2>/dev/null || true

echo "Storage permissions updated."
