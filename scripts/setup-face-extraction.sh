#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

echo "Installing Node dependencies..."
npm ci

echo "Building native canvas module..."
(cd node_modules/canvas && npx --yes node-gyp rebuild)

echo "Face extraction dependencies ready."
