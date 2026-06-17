# Referensi Pemerintah & Benchmark RT/RW — RT-008

Dokumen acuan untuk pengembangan **Layanan Warga RT-008** (`layananwarga.my.id`).

Ringkasan sistem untuk tugas akhir: [RINGKASAN-SISTEM.md](RINGKASAN-SISTEM.md) (termasuk diagram alur §13: warga, pengurus, layanan, WAHA).

## Dasar hukum

| Sumber | URL |
|--------|-----|
| UU No. 23/2006 (Adminduk) | https://peraturan.go.id |
| PP No. 40/2019 | https://peraturan.go.id |
| Permendagri No. 95/2019 (SIAK) | https://peraturan.go.id/id/permendagri-no-95-tahun-2019 |
| Permendagri No. 138/2017 (PTSP) | https://peraturan.go.id/id/permendagri-no-138-tahun-2017 |

## Portal Kemendagri / Dukcapil

| Sumber | URL |
|--------|-----|
| Ditjen Dukcapil | https://dukcapil.kemendagri.go.id |
| Layanan Dukcapil | https://dukcapil.kemendagri.com/layanan/ |
| Aplikasi & sistem | https://dukcapil.kemendagri.com/aplikasi/ |
| Dukcapil Online | https://dukcapil.online/ |
| E-PRODESKEL Data RT/RW | https://e-prodeskel.kemendagri.go.id/v/2025/data-tema/11/data-rt-rw |
| SIOLA | https://ditjenbinaadwil.kemendagri.go.id/layanan |

## Pemerintah daerah (pola layanan)

| Sumber | URL |
|--------|-----|
| Dukcapil DKI | https://kependudukancapil.jakarta.go.id/pelayanan/ |
| Pemkot Kediri (adminduk kelurahan) | https://kedirikota.go.id |
| BAPENDA Jabar (WA chatbot) | https://bapenda.jabarprov.go.id/permohonan-informasi-melalui-wa-chat-bot/ |
| Imigrasi Banggai (notifikasi WA) | https://banggai.imigrasi.go.id |

## Benchmark platform RT/RW

| Platform | URL |
|----------|-----|
| RTRW Online | https://www.rtrwonline.id/ |
| Ruang Warga | https://www.ruangwarga.id/public/services/citizen |
| SiDakRT | https://sidakrt.com |
| Template surat RT/RW | https://kumpulrejo.salatiga.go.id/template-surat-pengantar-rt-rw/ |

## Field wajib surat pengantar RT/RW

Nama, tempat/tanggal lahir, jenis kelamin, status perkawinan, kewarganegaraan, NIK/KK, agama, pekerjaan, alamat, keperluan — ditandatangani Ketua RT dan RW.

## Kebijakan proyek ini (fase 1)

- Mandiri tanpa API Dukcapil/SIAK.
- WhatsApp (WAHA): notifikasi perubahan status permohonan saja.
- Surat RT bersifat **pengantar**; dokumen resmi di kelurahan/Dukcapil.
