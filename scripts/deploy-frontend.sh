#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "→ Build asset Vite..."
docker compose exec -T app npm run build

echo "→ Bersihkan cache view Blade..."
docker compose exec -T app php artisan view:clear

echo "→ Bersihkan cache config..."
docker compose exec -T app php artisan config:clear

echo "Deploy frontend selesai."
