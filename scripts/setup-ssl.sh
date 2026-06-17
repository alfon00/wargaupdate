#!/bin/bash
# Pasang / perbarui sertifikat Let's Encrypt untuk layananwarga.my.id
set -euo pipefail

WEBROOT="/home/ubuntu/layananwarga/certbot/www"
mkdir -p "$WEBROOT"

if ! command -v certbot >/dev/null 2>&1; then
  sudo apt-get update -qq
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq certbot
fi

cd /home/ubuntu/layananwarga

# HTTP sementara untuk validasi ACME (mount default.conf jika SSL belum aktif)
sudo docker compose up -d nginx

sudo certbot certonly --webroot -w "$WEBROOT" \
  -d layananwarga.my.id \
  -d wa.layananwarga.my.id \
  --email "${CERTBOT_EMAIL:-admin@layananwarga.my.id}" \
  --agree-tos \
  --non-interactive \
  --expand

echo "Sertifikat siap. Pastikan docker-compose.yml memuat ssl-*.conf dan /etc/letsencrypt."
sudo docker compose up -d nginx
echo "Selesai. Uji: https://layananwarga.my.id dan https://wa.layananwarga.my.id"
