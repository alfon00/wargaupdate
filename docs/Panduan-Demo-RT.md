# Panduan Demo Portal ke Pengurus RT

**Layanan Warga RT** · [layananwarga.my.id](https://layananwarga.my.id) · Juni 2026

Dokumen ini merangkum pertanyaan dan alur demo portal untuk pengurus RT. Fokusnya bukan teknis, melainkan **apakah portal membantu alur kerja harian RT**.

---

## Aktor dalam Sistem (ruang lingkup demo)

| Aktor | Peran | Didemo? |
|-------|-------|---------|
| **Warga** | Mengajukan surat, pendataan, pengaduan, lacak status — tanpa login | Contoh pengajuan & lacak |
| **Ketua RT** | Verifikasi permohonan, susun & terbitkan surat PDF, kelola data warga, kegiatan, laporan | Ya (utama) |
| **Sekretaris RT** | Sama seperti Ketua RT sesuai akun yang diberikan admin | Opsional |
| **Admin sistem** | Setup akun pengurus, profil RT, katalog layanan | Tidak (hanya jelaskan singkat) |
| **Petugas / panel Kelurahan** | — | **Tidak digunakan** |

> **Catatan:** Operasional harian portal hanya melibatkan **warga ↔ pengurus RT**. Tidak ada panel kelurahan yang dipakai dalam alur layanan surat, pendataan, atau pengaduan. Profil lurah di halaman publik hanya informasi wilayah, bukan aktor proses layanan.

---

## 1. Pembuka (±5 menit)

*Tujuan: pahami kondisi RT sekarang sebelum demo.*

- Saat ini permohonan surat dari warga ditangani bagaimana? (WhatsApp, datang langsung, kertas?)
- Berapa lama dari warga mengajuan sampai surat siap?
- Bagian mana yang paling merepotkan: verifikasi berkas, tulis surat, TTD, cap/stempel, atau kabari warga?
- Siapa yang mengerjakan: Ketua RT, Sekretaris, atau bergantian?

---

## 2. Demo Permohonan Surat (fokus utama)

*Portal menerbitkan surat pengantar RT dalam bentuk PDF dengan TTD digital dan cap RT.*

### Alur yang ditunjukkan

1. **Warga** mengajukan via portal (verifikasi NIK, RT, nomor HP) + unggah KK/KTP
2. **RT** terima atau tolak permohonan (tolak → notifikasi WhatsApp ke warga)
3. **RT** susun surat: nomor surat, data pemohon (terisi otomatis), keperluan
4. **RT** gambar TTD di kanvas; unggah cap RT via Pengaturan (jika belum ada)
5. **RT** preview PDF → terbitkan surat
6. Notifikasi WhatsApp otomatis ke warga; PDF dapat dikirim ulang via WhatsApp bila perlu
7. Salinan fisik tetap dapat diambil di sekretariat RT

### Jenis surat yang tersedia

Domisili · SKTM (tidak mampu) · Usaha (SKU) · Pengantar KK · Pengantar KTP · Pengantar SKCK · Umum

### Pertanyaan ke RT saat demo surat

- Apakah alur **terima → susun → TTD → terbitkan PDF** sudah sesuai cara kerja RT?
- Kop surat, nomor surat (format *RTxxx / … / tahun*), tanggal, dan teks penutup — sudah mirip surat RT yang dipakai?
- Posisi dan ukuran **TTD + cap/stempel** di PDF — sudah layak dipakai resmi?
- Nama Ketua RT di bagian TTD — sudah benar (bukan label generik “Ketua RT …”)?
- Setelah PDF terbit, warga cukup terima via WhatsApp atau wajib ambil salinan fisik?
- Apakah perlu terbitkan ulang jika ada salah ketik, atau cukup sekali terbit?
- Jenis surat mana yang paling sering diajukan dan layak jadi **pilot pertama**?

---

## 3. Demo Pendataan & Data Warga

- Verifikasi warga baru / pendataan ulang — lebih mudah atau lebih ribet dari manual?
- Data warga di panel RT — sudah lengkap untuk administrasi?
- Sekretaris RT perlu akses penuh atau cukup Ketua saja?

---

## 4. Demo Kegiatan, Pengumuman & WhatsApp

- Kirim ke **semua warga** vs **pilih warga tertentu** — mana yang lebih sering dipakai?
- Isi notifikasi WhatsApp — sudah jelas untuk warga?

---

## 5. Demo Laporan / Pengaduan Warga

- Status **sedang ditindak** dan **selesai** — sesuai alur RT?
- Notifikasi WhatsApp otomatis saat status disimpan — sudah cukup?

---

## 6. Penutup — Pertanyaan Keputusan

- Fitur mana yang **paling berguna** minggu depan?
- Fitur mana yang **belum bisa dipakai** (terutama format/layout surat)?
- RT siap go-live untuk jenis surat apa dulu?
- Siapa **operator utama** (Ketua/Sekretaris)?
- Perlu pelatihan singkat (30–60 menit)?

### 3 pertanyaan kunci (jika waktu singkat)

1. “Alur surat tadi — sudah bisa dipakai untuk surat resmi RT?”
2. “Bagian surat PDF mana yang masih perlu disesuaikan (kop, TTD, cap, nomor)?”
3. “Kalau live minggu depan, surat apa yang jadi pilot pertama?”

---

## Alur Demo yang Disarankan (±20 menit)

| Langkah | Menu / Fitur | Perhatian saat demo |
|--------|--------------|---------------------|
| 1 | Login panel RT | Akun Ketua RT terhubung ke profil RT yang benar |
| 2 | Permohonan surat | Terima/tolak; cek lampiran KK/KTP warga |
| 3 | Susun & terbitkan surat | Nomor surat, preview PDF, TTD kanvas, cap RT |
| 4 | Cetak / WhatsApp PDF | Notifikasi otomatis + kirim PDF ke warga |
| 5 | Lacak permohonan (warga) | Warga cek status & nomor surat tanpa login |
| 6 | Kegiatan / pengumuman | Broadcast semua warga atau pilih tertentu |
| 7 | Laporan warga | Ubah status → notifikasi otomatis ke pelapor |

---

## Tips sesi demo

Gunakan contoh nyata (mis. surat domisili RT 008). Pastikan cap RT sudah diunggah via *Pengaturan* di menu Permohonan. Biarkan Ketua RT **klik sendiri**. Catat masukan surat: cocok / perlu revisi / belum bisa dipakai. Akhiri dengan kesepakatan **jenis surat pilot** dan tanggal go-live.

---

*Layanan Warga RT — Portal surat pengantar & layanan administrasi warga · Bukan situs Dukcapil/Kemendagri · Panel kelurahan tidak digunakan*
