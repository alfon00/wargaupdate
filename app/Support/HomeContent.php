<?php

namespace App\Support;

final class HomeContent
{
    public static function heroTagline(): string
    {
        return config('kelurahan.portal_hero_tagline');
    }

    public static function platformIntroLead(): string
    {
        return 'Portal terbuka untuk warga. Ajukan surat pengantar RT, pendataan ulang, atau pendataan warga secara online — tanpa login. Warga terdata memverifikasi identitas dengan NIK dan nomor HP sebelum melanjutkan layanan surat.';
    }

    /** @return list<array{title: string, desc: string}> */
    public static function platformAdvantages(): array
    {
        return [
            [
                'title' => 'Cepat & praktis',
                'desc' => 'Pengajuan layanan administrasi dapat dilakukan online kapan saja, tanpa antre di kantor RT untuk tahap awal.',
            ],
            [
                'title' => 'Transparan',
                'desc' => 'Status permohonan dapat dipantau; warga mendapat pembaruan proses layanan.',
            ],
            [
                'title' => 'Akurat & terintegrasi',
                'desc' => 'Verifikasi identitas dan data warga terpusat di sistem RT berbasis cloud.',
            ],
        ];
    }

    /** @return list<array{title: string, desc: string}> */
    public static function processingSteps(): array
    {
        return self::serviceCatalogFlows()['surat']['steps'];
    }

    /**
     * @return array<string, array{label: string, intro: string, anchor: string, steps: list<array{title: string, desc: string}>}>
     */
    public static function serviceCatalogFlows(): array
    {
        return [
            'surat' => [
                'label' => 'Surat pengantar RT',
                'anchor' => 'alur-surat',
                'intro' => '',
                'steps' => [
                    [
                        'title' => 'Pilih jenis surat',
                        'desc' => 'Pilih jenis surat, baca persyaratan, lalu klik Ajukan.',
                    ],
                    [
                        'title' => 'Verifikasi identitas',
                        'desc' => 'Masukkan NIK, RT, dan nomor HP terdaftar.',
                    ],
                    [
                        'title' => 'Lengkapi permohonan',
                        'desc' => 'Isi keperluan dan unggah berkas pendukung.',
                    ],
                    [
                        'title' => 'Surat siap',
                        'desc' => 'RT memverifikasi dan menerbitkan PDF. Notifikasi WhatsApp; Ambil salinan fisik di sekretariat RT bila perlu. Lacak status via menu Lacak.',
                    ],
                ],
            ],
            'pendataan_ulang' => [
                'label' => 'Pendataan ulang',
                'anchor' => 'alur-pendataan-ulang',
                'intro' => '',
                'steps' => [
                    [
                        'title' => 'Verifikasi identitas',
                        'desc' => 'NIK kepala KK, RT, dan nomor HP terdaftar.',
                    ],
                    [
                        'title' => 'Unggah berkas',
                        'desc' => 'Scan KK terbaru dan KTP/KIA setiap anggota keluarga.',
                    ],
                    [
                        'title' => 'Verifikasi pengurus RT',
                        'desc' => 'Pengurus RT memeriksa berkas melalui panel pendataan.',
                    ],
                    [
                        'title' => 'Data diperbarui',
                        'desc' => 'Setelah disetujui, data keluarga diperbarui di sistem RT.',
                    ],
                ],
            ],
            'pendataan_warga' => [
                'label' => 'Pendataan warga',
                'anchor' => 'alur-pendataan-warga',
                'intro' => '',
                'steps' => [
                    [
                        'title' => 'Isi data keluarga',
                        'desc' => 'Lengkapi data KK, alamat, dan anggota sesuai dokumen.',
                    ],
                    [
                        'title' => 'Unggah berkas & verifikasi wajah',
                        'desc' => 'Unggah KK serta KTP/KIA tiap anggota. Kepala KK melakukan verifikasi wajah.',
                    ],
                    [
                        'title' => 'Verifikasi pengurus RT',
                        'desc' => 'Pengurus RT memeriksa kelengkapan berkas sebelum data aktif.',
                    ],
                    [
                        'title' => 'Keluarga tercatat',
                        'desc' => 'Setelah disetujui, keluarga aktif di sistem RT.',
                    ],
                ],
            ],
        ];
    }

    /** @return list<array{question: string, answer: string}> */
    public static function faq(): array
    {
        return [
            [
                'question' => 'Bagaimana cara mengajukan surat pengantar RT?',
                'answer' => 'Buka Layanan, pilih Surat pengantar RT, tentukan jenis surat, baca persyaratan, klik Ajukan, verifikasi NIK dan nomor HP, lalu lengkapi formulir permohonan.',
            ],
            [
                'question' => 'Bagaimana cara mengambil surat yang sudah jadi?',
                'answer' => 'Setelah pengurus RT menerbitkan surat PDF, Anda mendapat notifikasi WhatsApp (jika nomor aktif). Pengurus dapat mengirim PDF via WhatsApp. Ambil salinan fisik di sekretariat RT bila diperlukan. Lacak status dan nomor surat di menu Lacak Permohonan.',
            ],
            [
                'question' => 'Apa saja dokumen yang perlu disiapkan?',
                'answer' => 'KTP dan KK warga terdata di RT, plus dokumen pendukung sesuai jenis surat — untuk pengajuan surat pengantar RT, bukan dokumen resmi Dukcapil.',
            ],
            [
                'question' => 'Apakah warga baru wajib melakukan pendataan?',
                'answer' => 'Ya. Keluarga belum terdata dapat mengajukan pendataan warga melalui portal. Setelah diverifikasi RT, data aktif dan warga dapat mengajukan surat pengantar.',
            ],
            [
                'question' => 'Apakah layanan di portal ini berbayar?',
                'answer' => 'Tidak. Pengajuan surat pengantar RT, pendataan, dan fitur portal lainnya gratis untuk warga. Portal tidak meminta pembayaran, transfer, atau OTP. Biaya di instansi berwenang (misalnya Dukcapil) jika ada tetap di luar portal ini.',
            ],
            [
                'question' => 'Berapa lama proses layanan?',
                'answer' => 'Proses bergantung pada kelengkapan data dan waktu verifikasi pengurus RT. Pantau perkembangan melalui Lacak Permohonan atau notifikasi WhatsApp.',
            ],
        ];
    }
}
