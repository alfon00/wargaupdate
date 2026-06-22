<?php

return [

    'portal_nama' => 'Layanan Warga RT',
    'portal_hero_tagline' => 'Di Kelurahan Inauga · Kabupaten Mimika',
    'portal_subtitle' => 'Di Kelurahan Inauga · Kabupaten Mimika',
    'portal_subtitle_nav' => '',
    'portal_nama_schema' => 'Portal Layanan Warga RT',

    'nama' => '',
    'distrik' => 'Distrik Wania',
    'kabupaten' => 'Kabupaten Mimika',
    'provinsi' => 'Papua Tengah',

    'staff_email_domain' => env('STAFF_EMAIL_DOMAIN', 'layananwarga.my.id'),

    'portal_logo' => env('PORTAL_LOGO', 'images/brand/logo-rt.webp'),

    /*
    | Kop surat pengantar RT (format resmi desa/kelurahan).
    | Logo pemda dipakai di semua surat; teks desa/kecamatan diisi dari profil RT.
    */
    'letter_kop' => [
        'logo' => env('LETTER_KOP_LOGO', 'images/brand/logo-kabupaten-mimika.png'),
        'dusun' => env('LETTER_KOP_DUSUN', ''),
        'kode_pos' => env('LETTER_KOP_KODE_POS', ''),
        'alamat_kantor_default' => env('LETTER_KOP_ALAMAT', ''),
    ],
    'portal_favicon' => env('PORTAL_FAVICON', 'images/brand/favicon-rt.png'),
    'portal_apple_touch' => env('PORTAL_APPLE_TOUCH', 'images/brand/apple-touch-rt.png'),
    'hero_beranda_image' => env('KELURAHAN_HERO_BERANDA', 'images/hero/beranda-hero-inauga.png'),

    'penjelasan_wilayah' => 'Seluruh RT dan RW di halaman ini berada di Distrik Wania, Papua Tengah.',

    'face_match_threshold' => (float) env('FACE_MATCH_THRESHOLD', 0.6),

    'pendataan_min_anggota_keluarga' => 2,
    'pendataan_max_anggota' => 50,

    'layanan_persyaratan' => [
        'pendataan_ulang' => [
            'Verifikasi identitas dengan NIK 16 digit, RT, dan nomor HP/WhatsApp keluarga yang terdaftar.',
            'Unggah scan/foto KK serta KTP atau KIA setiap anggota keluarga (PDF/JPG/PNG, maks. 5 MB per berkas).',
            'Pengurus RT memeriksa berkas dan memperbarui data lewat panel verifikasi.',
            'Pengajuan diterima atau ditolak dengan notifikasi WhatsApp.',
        ],
        'pendataan_warga' => [
            'Untuk keluarga yang belum terdata di RT. Siapkan scan/foto Kartu Keluarga dan KTP/KIA setiap anggota.',
            'Isi data sesuai KK dan KTP/KIA: nomor KK, alamat tempat tinggal, dan identitas tiap anggota (nama, NIK, TTL, demografi).',
            'Verifikasi wajah kepala keluarga: ambil foto selfie langsung di kamera.',
            'Pilih RT domisili dan isi nomor HP/WhatsApp keluarga untuk notifikasi status.',
            'Pengurus RT memverifikasi berkas sebelum data dinyatakan aktif.',
            'Bukan layanan penerbitan KK/KTP resmi — hanya pencatatan data warga di RT.',
        ],
        'kontak_pengaduan' => [
            'Pilih RT wilayah kejadian dan jenis masalah lingkungan.',
            'Isi nama pelapor, nomor HP, lokasi kejadian, dan uraian pengaduan.',
            'NIK (16 digit) dan foto bukti bersifat opsional.',
            'Centang pernyataan kebenaran laporan sebelum mengirim.',
        ],
        'surat' => [
            'Warga harus sudah terdata dan diverifikasi pengurus RT.',
            'NIK 16 digit dan nomor HP harus cocok dengan data terdaftar.',
            'Data kependudukan (termasuk pekerjaan, pendidikan, agama) harus lengkap sebelum mengajukan surat.',
            'Portal ini memfasilitasi permohonan surat pengantar RT; surat fisik dicetak di sekretariat RT — bukan KK, KTP, atau dokumen resmi Dukcapil.',
        ],
        'apply' => [
            'Identitas pemohon harus sudah terverifikasi (NIK, RT, nomor HP).',
            'Isi keperluan/keterangan surat dengan jelas.',
            'Unggah dokumen pendukung jika diminta untuk jenis surat tersebut.',
            'Surat pengantar diambil di sekretariat RT setelah disetujui.',
        ],
        'berkas_surat' => ['KK', 'KTP'],
    ],

    'letter_max_subjects' => 10,

    'resident_demographics' => [
        'religions' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'],
        'education_levels' => ['Tidak sekolah', 'TK', 'SD', 'SMP', 'SMA/SMK', 'Diploma', 'S1', 'S2', 'S3'],
        'marital_statuses' => ['Belum kawin', 'Kawin', 'Cerai hidup', 'Cerai mati'],
        'citizenships' => ['WNI', 'WNA'],
        'occupations' => [
            'Belum/tidak bekerja',
            'Mengurus rumah tangga',
            'Pelajar/Mahasiswa',
            'Petani/Pekebun',
            'Peternak',
            'Nelayan',
            'Buruh',
            'Pedagang',
            'Wiraswasta',
            'Karyawan swasta',
            'PNS',
            'TNI/Polri',
            'Pegawai BUMN/BUMD',
            'Guru',
            'Tenaga kesehatan',
            'Pensiunan',
            'Tukang',
            'Sopir/Ojek',
            'Lainnya',
        ],
    ],

    'laporan_kategori' => [
        'permohonan' => 'Kendala permohonan surat',
        'pendataan' => 'Kendala pendataan / data warga',
        'portal' => 'Masalah teknis portal',
        'layanan_rt' => 'Keluhan layanan RT',
        'pengaduan_lingkungan' => 'Pengaduan lingkungan',
        'umum' => 'Pertanyaan umum',
        'lainnya' => 'Lainnya',
    ],

    'pengaduan_jenis' => [
        'sampah' => 'Sampah & kebersihan',
        'kebisingan' => 'Kebisingan',
        'drainase' => 'Drainase & banjir',
        'jalan' => 'Jalan & trotoar',
        'penerangan' => 'Penerangan jalan',
        'lainnya' => 'Lainnya',
    ],

    'maps_embed_url' => env('KELURAHAN_MAPS_EMBED', ''),

    'kontak_jam_default' => 'Senin–Jumat 08.00–14.00 WIT',

    'whatsapp_admin_note' => 'Nomor WhatsApp hanya digunakan untuk urusan administrasi RT.',

    'track_faq' => [
        [
            'question' => 'Di mana saya menemukan nomor permohonan?',
            'answer' => 'Nomor permohonan ditampilkan setelah Anda mengajukan surat pengantar. Nomor yang sama juga dikirim melalui notifikasi WhatsApp. Buka menu Lacak Permohonan dan masukkan nomor tersebut untuk melihat progres.',
        ],
        [
            'question' => 'Bagaimana cara mengetahui ada pembaruan status?',
            'answer' => 'Anda akan mendapat notifikasi WhatsApp jika nomor HP aktif dan notifikasi diaktifkan. Kapan saja, buka halaman Lacak Permohonan dan masukkan nomor permohonan untuk melihat status terbaru.',
        ],
        [
            'question' => 'Berapa lama proses surat pengantar?',
            'answer' => 'Tergantung kelengkapan berkas dan antrian di RT. Lacak status secara berkala di halaman ini.',
        ],
        [
            'question' => 'Surat sudah siap, apa yang harus dilakukan?',
            'answer' => 'Surat PDF sudah diterbitkan RT. Nomor surat tampil di halaman Lacak dan dikirim lewat notifikasi WhatsApp. Ambil salinan fisik di sekretariat RT bila diperlukan, atau cek WhatsApp jika pengurus mengirim PDF.',
        ],
        [
            'question' => 'Apakah portal ini menerbitkan KTP atau KK?',
            'answer' => 'Tidak. Portal ini hanya memfasilitasi permohonan surat pengantar RT. Dokumen resmi diterbitkan instansi berwenang.',
        ],
    ],

    'wa_letter' => "Yth. {nama},\n\nSurat pengantar *{layanan}* ({no}) dari {rt} terlampir.\nNomor surat: {nomor_surat}\n\nUnduh surat: {link_surat}\n\n— {portal}",

    'wa_letter_attached' => "Yth. {nama},\n\nSurat pengantar *{layanan}* ({no}) dari {rt} terlampir.\nNomor surat: {nomor_surat}\n\n— {portal}",

    'wa_permohonan' => [
        'submitted' => "Yth. {nama},\n\nPermohonan *{layanan}* ({no}) di {rt} telah *diterima* dan *menunggu verifikasi* pengurus RT.\n\nLacak: {url}/lacak\n\n— {portal}",
        'verified' => "Yth. {nama},\n\nPermohonan *{layanan}* ({no}) di {rt} telah *diterima* pengurus RT.\nPengurus sedang menyusun surat pengantar.\n\nLacak: {url}/lacak\n\n— {portal}",
        'approved' => "Yth. {nama},\n\nSurat pengantar *{layanan}* ({no}) di {rt} telah *siap*.\n\nLacak nomor surat: {url}/lacak\nAmbil salinan fisik di sekretariat RT bila diperlukan, atau cek WhatsApp jika pengurus mengirim PDF.\n\n— {portal}",
        'rejected' => "Yth. {nama},\n\nPermohonan *{layanan}* ({no}) di {rt} *ditolak*.\n{catatan}\n\n— {portal}",
    ],

    'wa_pendataan' => [
        'submitted' => "Yth. {nama},\n\nData pendaftaran Anda telah diterima {rt} dan *menunggu verifikasi* pengurus.\n\n— {portal}",
        'verified' => "Yth. {nama},\n\nPendaftaran Anda di {rt} Kelurahan Inauga telah *diverifikasi* dan *lengkap*.\nAnda sudah terdata sebagai warga {rt}.\n\nPortal: {url}/layanan\n\n— {portal}",
        'rejected' => "Yth. {nama},\n\nPengajuan pendataan Anda di {rt} *ditolak*.\n{catatan}\n\nSilakan periksa berkas dan ajukan ulang:\n{layanan_url}\n\n— {portal}",
        'registered_by_rt' => "Yth. {nama},\n\nData kependudukan di {rt} telah *dicatat* oleh pengurus RT.\n{detail}Portal: {url}/layanan\n\n— {portal}",
    ],

    'wa_laporan' => [
        'submitted' => "Yth. {nama},\n\nLaporan *{no}* ke {rt} telah *diterima*.\nPerihal: {perihal}\n\nPengurus RT akan menindaklanjuti.\n\n— {portal}",
        'status_updated' => "Yth. {nama},\n\nLaporan *{no}* ({perihal}) di {rt} status: *{status}*.\n{catatan}\n\n— {portal}",
    ],

    'wa_publikasi' => [
        'kegiatan' => "Yth. {nama},\n\n*{rt}* mengumumkan kegiatan:\n*{judul}*\nTanggal: {tanggal}\nLokasi: {lokasi}\n\n{ringkasan}\n\nPortal: {url}/kegiatan\n\n— {portal}",
        'pengumuman' => "Yth. {nama},\n\n*{rt}* — Pengumuman:\n*{judul}*\n\n{ringkasan}\n\nPortal: {url}/kegiatan\n\n— {portal}",
    ],

    'lurah' => [
        'jabatan' => 'Lurah',
        'nama' => 'Gerson Rumbarar, S.E.',
        'photo' => 'images/kelurahan/lurah.png',
        'telepon' => null,
        'whatsapp' => null,
        'email' => null,
        'alamat_kantor' => 'Distrik Wania',
        'jam_layanan' => 'Senin–Jumat 08.00–14.00 WIT',
        'visi' => 'Mewujudkan pelayanan publik yang transparan, responsif, dan mendukung kesejahteraan warga setempat.',
        'misi' => '1. Mengkoordinasikan RT/RW dalam administrasi kependudukan. 2. Memastikan surat pengantar dan layanan publik berjalan tertib. 3. Menjalin sinergi dengan distrik dan pemerintah kabupaten.',
    ],

    'sosial' => [
        'instagram' => env('KELURAHAN_INSTAGRAM_URL'),
        'facebook' => env('KELURAHAN_FACEBOOK_URL'),
        'youtube' => env('KELURAHAN_YOUTUBE_URL'),
    ],

    'kegiatan' => [],

    /*
    | Field formulir penyusunan surat RT (per kode layanan).
    | Field umum (_common) digabung dengan field khusus layanan.
    */
    'letter_fields' => [
        '_common' => [
            ['key' => 'nama', 'label' => 'Nama lengkap pemohon', 'type' => 'text', 'required' => true],
            ['key' => 'nik', 'label' => 'NIK', 'type' => 'text', 'required' => true],
            ['key' => 'ttl', 'label' => 'Tempat, tanggal lahir', 'type' => 'text', 'required' => true],
            ['key' => 'jenis_kelamin', 'label' => 'Jenis kelamin', 'type' => 'text', 'required' => true],
            ['key' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text', 'required' => false],
            ['key' => 'no_ktp_kk', 'label' => 'No. KTP / KK', 'type' => 'text', 'required' => true],
            ['key' => 'kewarganegaraan', 'label' => 'Kewarganegaraan', 'type' => 'text', 'required' => false],
            ['key' => 'pendidikan', 'label' => 'Pendidikan', 'type' => 'text', 'required' => false],
            ['key' => 'agama', 'label' => 'Agama', 'type' => 'text', 'required' => false],
            ['key' => 'status_perkawinan', 'label' => 'Status perkawinan', 'type' => 'text', 'required' => false],
            ['key' => 'alamat', 'label' => 'Alamat tempat tinggal', 'type' => 'textarea', 'required' => true],
            ['key' => 'rt_rw', 'label' => 'RT / RW', 'type' => 'text', 'required' => true],
            ['key' => 'keperluan', 'label' => 'Maksud / keperluan surat', 'type' => 'textarea', 'required' => true],
        ],
    ],

];
