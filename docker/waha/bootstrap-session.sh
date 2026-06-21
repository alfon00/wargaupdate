#!/bin/sh
set -eu

API_KEY="${WAHA_API_KEY_PLAIN:?WAHA_API_KEY_PLAIN required}"
BASE_URL="${WAHA_INTERNAL_URL:-http://waha:3000}"
SESSION="${WAHA_SESSION:-default}"

echo "Waiting for WAHA at ${BASE_URL}..."
for i in $(seq 1 30); do
  if curl -sf "${BASE_URL}/ping" >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

status="$(curl -sf -H "X-Api-Key: ${API_KEY}" "${BASE_URL}/api/sessions/${SESSION}" | sed -n 's/.*"status":"\([^"]*\)".*/\1/p' || true)"
echo "Session ${SESSION} status: ${status:-unknown}"

if [ "${status}" = "WORKING" ]; then
echo "Session already working."
exit 0
fi

if [ -x /var/www/html/scripts/waha-sync-dashboard.sh ] 2>/dev/null; then
  /var/www/html/scripts/waha-sync-dashboard.sh || true
elif [ -x ./scripts/waha-sync-dashboard.sh ]; then
  ./scripts/waha-sync-dashboard.sh || true
fi

echo "Starting session ${SESSION}..."
curl -sf -X POST -H "X-Api-Key: ${API_KEY}" -H "Content-Type: application/json" \
  "${BASE_URL}/api/sessions/${SESSION}/start" >/dev/null || true

for i in $(seq 1 30); do
  status="$(curl -sf -H "X-Api-Key: ${API_KEY}" "${BASE_URL}/api/sessions/${SESSION}" | sed -n 's/.*"status":"\([^"]*\)".*/\1/p' || true)"
  echo "  poll ${i}: ${status:-unknown}"
  if [ "${status}" = "WORKING" ]; then
    echo "Session started successfully."
    exit 0
  fi
  if [ "${status}" = "SCAN_QR_CODE" ]; then
    echo "Session needs QR scan — open https://wa.layananwarga.my.id/dashboard/"
    exit 0
  fi
  sleep 3
done

echo "Warning: session did not reach WORKING within timeout."
exit 0
