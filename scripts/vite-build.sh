#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

node_meets_vite_requirement() {
    node -e '
        const [major, minor] = process.versions.node.split(".").map(Number);
        const ok = major > 22 || (major === 22 && minor >= 12) || (major === 20 && minor >= 19);
        process.exit(ok ? 0 : 1);
    ' 2>/dev/null
}

if node_meets_vite_requirement; then
    exec npx vite build "$@"
fi

if ! command -v docker >/dev/null 2>&1; then
    echo "Error: Node $(node -v) tidak memenuhi syarat Vite 8 (^20.19.0 atau >=22.12.0)." >&2
    echo "Install Docker untuk build otomatis, atau upgrade Node ke 20.19+ / 22.12+." >&2
    exit 1
fi

echo "Node $(node -v) terlalu lama — build via Docker (node:22-alpine)..." >&2
exec docker run --rm \
    -v "$ROOT:/app" \
    -w /app \
    node:22-alpine \
    sh -c "npm ci && npx vite build $*"
