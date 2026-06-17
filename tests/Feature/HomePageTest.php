<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_does_not_display_statistics_section(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Data warga terdaftar di portal', false);
        $response->assertSee('Warga yang sudah terdata', false);
        $response->assertSee('sudah terdata di sistem RT', false);
        $response->assertSee('Portal terbuka untuk semua warga', false);
        $response->assertDontSee('lw-home-stats', false);
        $response->assertDontSee('lw-home-stat-card', false);
        $response->assertDontSee('lw-ui-flat', false);
        $response->assertDontSee('lw-home-quick-actions', false);
        $response->assertDontSee('Mulai dari sini', false);
        $response->assertSee('lw-home-feature-card', false);
        $response->assertSee('Fitur utama', false);
        $response->assertSee('Layanan Administrasi', false);
        $response->assertSee('Pendataan Ulang Warga', false);
        $response->assertSee('Pengaduan Warga', false);
        $response->assertSee('Lacak Permohonan', false);
        $response->assertSee('Pengumuman RT', false);
        $response->assertSee('Notifikasi WhatsApp', false);
        $response->assertSee('Pengajuan surat secara online', false);
        $response->assertSee('nomor tiket', false);
        $response->assertDontSee('Mudah Diakses', false);
        $response->assertDontSee('Alur Lebih Jelas', false);
        $response->assertDontSee('Data Warga Lebih Tertata', false);
        $response->assertSee('/lacak', false);
        $response->assertSee('/kontak', false);
        $response->assertSee('/kegiatan', false);
        $response->assertSee('Mengenal Platform Layanan RT Inauga', false);
        $response->assertSee('Keunggulan sistem', false);
        $response->assertSee('lw-home-advantage-card', false);
        $response->assertSee('Cepat &amp; praktis', false);
        $response->assertSee('Transparan', false);
        $response->assertSee('Akurat &amp; terintegrasi', false);
        $response->assertDontSee('Panduan Penggunaan Layanan', false);
        $response->assertDontSee('id="panduan"', false);
        $response->assertDontSee('id="home-faq-heading"', false);
        $response->assertDontSee('id="home-alur-heading"', false);
        $response->assertDontSee('id="layanan-utama"', false);
        $response->assertDontSee('id="home-services-main-heading"', false);
        $response->assertDontSee('Dua layanan utama untuk warga', false);
        $response->assertDontSee('Alur Pelayanan Administrasi RT', false);
        $response->assertDontSee('Pilih Jenis Layanan', false);
        $response->assertDontSee('class="lw-hero-eyebrow"', false);
        $response->assertSeeInOrder([
            'id="home-hero-title"',
            'SISTEM LAYANAN WARGA RT',
        ], false);
        $response->assertSee('Di Kelurahan Inauga', false);
        $response->assertSee('lw-home-hero-v2-tagline--short', false);
        $response->assertDontSee('Layanan digital terintegrasi berbasis cloud', false);
        $response->assertDontSee('notifikasi otomatis melalui WhatsApp', false);
        $response->assertDontSee('Platform ini menyediakan layanan pengajuan surat', false);
        $response->assertDontSee('class="lw-home-hero-v2-points"', false);
        $response->assertDontSee('Panduan alur pelayanan', false);
        $response->assertSee('SISTEM LAYANAN WARGA RT', false);
        $response->assertDontSee('Lebih Mudah dan Terarah', false);
        $response->assertDontSee('Platform ini membantu warga', false);
        $response->assertDontSee('Ajukan Surat Pengantar', false);
        $response->assertDontSee('Bukan layanan Dukcapil', false);
        $response->assertSee('lw-home-hero-v2-actions--modern', false);
        $response->assertSee('class="lw-home-hero-v2-tagline', false);
        $response->assertSee('Ajukan layanan', false);
        $response->assertSee('Lihat pengumuman', false);
        $response->assertSee('/layanan', false);
        $response->assertSee('#activities-announce-heading', false);
        $response->assertSee('lw-home-hero-v2-content--modern', false);
        $response->assertSee('lw-home-hero-v3-shell--bg', false);
        $response->assertSee('--lw-home-hero-bg-image', false);
        $response->assertSee('min-height:clamp(20rem,45vmin,30rem)', false);
        $response->assertDontSee('<div class="lw-site-frame">', false);
    }
}
