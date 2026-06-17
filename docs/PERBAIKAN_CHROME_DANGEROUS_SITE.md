# Perbaikan peringatan Chrome "Dangerous site"

Peringatan di `https://layananwarga.my.id/masuk` berasal dari **Google Safe Browsing** (bukan error SSL atau bug Laravel). Chrome memblokir URL yang terdaftar sebagai phishing / social engineering.

## URL login resmi (gunakan ini)

| URL | Fungsi |
|-----|--------|
| **https://layananwarga.my.id/akses-pengurus** | Satu halaman masuk pengurus (email + kata sandi); panel RT / Kelurahan / Admin ditentukan otomatis dari akun |

Path lama **`/masuk`** dan **`/akses-pengurus/rt`** (serta `/kelurahan`, `/admin`) dialihkan permanen (301) ke `/akses-pengurus` — jangan dibagikan URL portal terpisah ke pengurus.

## Yang sudah dilakukan di server

- Login dipindah ke `/akses-pengurus` dengan layout khusus (satu disclaimer, identitas kelurahan jelas).
- Halaman `/keamanan` dengan kontak RT dan kebijakan data.
- `/.well-known/security.txt` dan header keamanan (HSTS, CSP, Referrer-Policy).
- Schema.org `Organization` + meta canonical di layout.

## Langkah wajib: minta review ke Google

Tanpa review, perubahan kode saja **tidak** langsung menghapus blokir Chrome.

### 1. Verifikasi domain di Google Search Console

1. Buka [Google Search Console](https://search.google.com/search-console).
2. Tambahkan properti **URL prefix**: `https://layananwarga.my.id`.
3. Verifikasi via DNS TXT atau file HTML di `public/`.

### 2. Periksa laporan Security Issues

Setelah terverifikasi, buka **Security & Manual Actions → Security issues**. Jika ada masalah phishing, perbaiki sesuai detail lalu klik **Request review**.

### 3. Laporkan situs salah diblokir

- [Report incorrect phishing warning](https://safebrowsing.google.com/safebrowsing/report_error/)

Isi contoh:

- **URL:** `https://layananwarga.my.id/akses-pengurus`
- **Penjelasan:** Portal resmi layanan administrasi RT Kelurahan Inauga, Mimika. Bukan situs Dukcapil/Kemendagri/bank. Surat pengantar RT saja, tidak ada pembayaran.

### 4. Waktu tunggu

Review biasanya **3–14 hari**. Setelah disetujui, peringatan Chrome hilang otomatis (bisa lebih cepat untuk path baru).

## Pencegahan flag ulang

- Jangan gunakan logo/branding Dukcapil, Kemendagri, atau bank.
- Bagikan link **`/akses-pengurus`** ke pengurus, bukan `/masuk`.
- Jangan kirim link login lewat SMS/WA massal tanpa konteks.
- Pastikan subdomain `wa.layananwarga.my.id` tidak terbuka tanpa autentikasi API.

## Cek status

- [Google Transparency Report — Safe Browsing](https://transparencyreport.google.com/safe-browsing/search?url=layananwarga.my.id)
