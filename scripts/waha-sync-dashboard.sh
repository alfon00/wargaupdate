#!/bin/sh
# Generate halaman perbaikan dashboard WAHA (single-instance, bukan worker eksternal).
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$ROOT/waha/.env"
OUT_FILE="$ROOT/docker/nginx/waha-dashboard-init.html"

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing $ENV_FILE" >&2
  exit 1
fi

# shellcheck disable=SC1090
. "$ENV_FILE"

API_KEY="${WAHA_API_KEY_PLAIN:-}"
if [ -z "$API_KEY" ]; then
  echo "WAHA_API_KEY_PLAIN not set in waha/.env" >&2
  exit 1
fi

# Escape for JS string
ESCAPED_KEY=$(printf '%s' "$API_KEY" | sed 's/\\/\\\\/g; s/"/\\"/g')

cat > "$OUT_FILE" <<EOF
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, nofollow">
  <title>Perbaiki dashboard WAHA</title>
</head>
<body>
  <p>Menyetel koneksi dashboard WAHA (instance tunggal)…</p>
  <script>
    (function () {
      var servers = [{
        id: 'waha_000000000000000001',
        name: 'WAHA',
        connection: {
          url: window.location.origin,
          key: "$ESCAPED_KEY"
        }
      }];
      localStorage.setItem('servers', JSON.stringify(servers));
      window.location.replace('/dashboard/');
    })();
  </script>
</body>
</html>
EOF

echo "Generated $OUT_FILE"
