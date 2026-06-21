# Deploy — Layanan Warga RT

## Deploy perubahan tampilan web (UI)

Setelah mengubah Blade, CSS inline (`lw-styles`), `resources/js`, atau `resources/css`, jalankan checklist ini agar perubahan langsung terlihat tanpa hard refresh (Ctrl+Shift+R):

```bash
# Satu perintah (disarankan)
./scripts/deploy-frontend.sh

# Atau manual:
docker compose exec app npm run build
docker compose exec app php artisan view:clear
docker compose exec app php artisan config:clear
```

| Jenis perubahan | Langkah wajib |
|---|---|
| Blade / `lw-styles` | `view:clear` |
| `resources/js` atau `resources/css` | `npm run build` |
| `config/*.php` | `config:clear` (jika pernah `config:cache`) |

**Jangan** jalankan `php artisan optimize` di server yang sering di-update tanpa prosedur clear — perintah itu meng-cache view Blade dan membuat perubahan HTML tertahan.

Verifikasi cepat setelah deploy:

```bash
ls -la public/build/assets/ | tail -5
curl -I https://layananwarga.my.id/lacak | grep -i cache-control
```

Header `Cache-Control: no-store` pada halaman HTML menandakan browser tidak menyimpan salinan lama. Asset Vite di `/build/assets/` memakai hash di nama file sehingga cache panjang aman setelah build.

Jika tampilan masih lama setelah langkah di atas:

```bash
docker compose restart app nginx
```

## Penyimpanan berkas privat

Lampiran pendataan (KK, KTP, dll.) dan dokumen permohonan surat disimpan di disk Laravel `local`:

- Root: `storage/app/private`
- Pendataan baru: `storage/app/private/pendataan/rt-{rt_profile_id}/household-{household_id}/`
- Permohonan surat: `storage/app/private/application-documents/{application_id}/`

Berkas **tidak** boleh disimpan di `storage/app/public` atau diakses tanpa login pengurus.

### Permission (Docker)

Setelah deploy atau update container, pastikan direktori storage dapat ditulis oleh PHP-FPM:

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R ug+rwx storage bootstrap/cache
docker compose exec app mkdir -p storage/app/private/pendataan
docker compose exec app chown -R www-data:www-data storage/app/private/pendataan
docker compose exec app ls -la storage/app/private/pendataan
```

Pastikan folder `pendataan` dimiliki `www-data` (bukan `root`) dan permission minimal `drwxrwx---`. Jika subfolder `pendataan` pernah terbuat sebagai root (mis. saat menjalankan artisan dari shell host), PHP-FPM tidak bisa menulis dan unggah berkas di edit warga gagal dengan *Unable to create a directory*.

Jika unggah pendataan gagal dengan pesan tersebut, jalankan perintah di atas pada service `app`, lalu uji ulang simpan edit warga dengan lampiran.

### Limit unggah berkas (PHP)

Konfigurasi `docker/php/uploads.ini` menetapkan `upload_max_filesize` dan `post_max_size` ke **64M** (selaras dengan nginx). Setelah mengubah file ini, rebuild container PHP:

```bash
docker compose build app queue
docker compose up -d app queue
docker compose exec app php -i | grep -E 'upload_max_filesize|post_max_size'
```

Tanpa rebuild, unggahan KK/KTP di atas ~2 MB dapat gagal dengan pesan validasi `uploaded`.

## Nginx dan container PHP (502 Bad Gateway)

Nginx menyimpan IP upstream PHP-FPM saat startup. Jika container `app` di-recreate (IP berubah) sementara `nginx` masih berjalan, situs dapat menampilkan **502 Bad Gateway**.

Konfigurasi di `docker/nginx/ssl-layananwarga.conf` sudah memakai resolver Docker (`127.0.0.11`) agar hostname `app` di-resolve ulang secara dinamis.

Jika 502 muncul setelah recreate container `app`:

```bash
docker compose restart nginx
curl -I https://layananwarga.my.id/
```

## WAHA (WhatsApp)

Notifikasi WhatsApp memakai WAHA (container `container-whatsapp`). Laravel memakai `WAHA_INTERNAL_URL=http://waha:3000` dan `WAHA_API_KEY` (plain) dari `.env` — harus cocok dengan `WAHA_API_KEY_PLAIN` di `waha /.env`.

### Dashboard (`https://wa.layananwarga.my.id/dashboard/`)

WAHA di server ini **instance tunggal** (bukan worker cluster). Jangan menambah `https://wa.layananwarga.my.id` sebagai worker eksternal di dashboard.

**Perbaiki koneksi dashboard (sekali):** buka

`https://wa.layananwarga.my.id/fix-waha-dashboard`

Halaman itu menyetel koneksi lokal (`window.location.origin`) + API key yang benar, lalu mengarahkan ke `/dashboard/`.

Login dashboard: user **`waha`** (password di `waha/.env` → `WAHA_DASHBOARD_PASSWORD`).

Regenerasi file init setelah ganti API key:

```bash
./scripts/waha-sync-dashboard.sh
docker compose up -d nginx
```

### Sesi `default` setelah restart container

WAHA Core tidak otomatis menyalakkan sesi. Setelah `docker compose up` jalankan bootstrap (atau start manual):

```bash
docker compose run --rm waha-bootstrap
# atau manual:
docker compose exec app curl -sS -X POST -H "X-Api-Key: $(grep WAHA_API_KEY .env | cut -d= -f2)" http://waha:3000/api/sessions/default/start
```

Cek status:

```bash
docker compose exec app curl -sS -H "X-Api-Key: $(grep WAHA_API_KEY .env | cut -d= -f2)" http://waha:3000/api/sessions/default
```

Harus `"status":"WORKING"`. Jika `"SCAN_QR_CODE"`, buka dashboard dan scan QR WhatsApp.

Jika sesi berstatus `FAILED`, restart container lalu jalankan bootstrap di atas.

## Verifikasi wajah layanan surat

Verifikasi wajah di `/layanan/surat` membandingkan selfie warga dengan referensi wajah yang diekstrak dari berkas KTP/KK di pendataan RT. Referensi dibuat otomatis saat RT mengunggah KK/KTP; jika ekstraksi gagal (PDF buram, wajah tidak terdeteksi, ImageMagick/Node.js bermasalah), berkas tetap tersimpan tetapi verifikasi wajah dapat gagal.

### Backfill referensi wajah (data lama)

Jalankan setelah deploy fitur verifikasi wajah atau jika warga melaporkan error *wajah tidak dapat dibaca* padahal KK/KTP sudah ada:

```bash
# Semua KK yang punya dokumen KTP/KK
docker compose exec app php artisan face:backfill-references

# Satu KK tertentu
docker compose exec app php artisan face:backfill-references --household=11
```

Pastikan berkas KTP/KK berupa foto JPG/PNG yang jelas (hindari PDF buram). Setelah backfill, minta warga coba verifikasi ulang di layanan surat.
