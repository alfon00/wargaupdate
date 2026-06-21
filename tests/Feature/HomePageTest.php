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
        $response->assertDontSee('lw-home-stats', false);
        $response->assertDontSee('lw-home-stat-card', false);
        $response->assertDontSee('lw-home-quick-actions', false);
        $response->assertDontSee('Mulai dari sini', false);
        $response->assertDontSee('<div class="lw-site-frame">', false);

        $response->assertSee('Portal terbuka untuk warga', false);
        $response->assertSee('Mengenal Layanan Warga RT', false);
        $response->assertSee('Fitur utama', false);
        $response->assertSee('Keunggulan sistem', false);
        $response->assertSee('Panduan Penggunaan Layanan', false);
        $response->assertSee('lw-home-feature-card', false);
        $response->assertSee('Surat pengantar RT', false);
        $response->assertSee('Pendataan ulang', false);
        $response->assertSee('Pendataan warga', false);

        $response->assertSee('Di Kelurahan Inauga', false);
        $response->assertSee('Kabupaten Mimika', false);
        $response->assertSee('Portal warga', false);
        $response->assertSee('Layanan Warga RT', false);
        $response->assertSee('lw-home-hero-v3-shell--bg', false);
        $response->assertSee('Ajukan layanan', false);

        $html = $response->getContent();
        preg_match('/<nav class="lw-nav"[^>]*>.*?<\/nav>/s', $html, $nav);
        preg_match('/<footer class="lw-footer"[^>]*>.*?<\/footer>/s', $html, $footer);
        $this->assertStringNotContainsString('Inauga', $nav[0] ?? '');
        $this->assertStringNotContainsString('Kelurahan', $nav[0] ?? '');
        $this->assertStringNotContainsString('Kabupaten Mimika', $nav[0] ?? '');
        $this->assertStringNotContainsString('Inauga', $footer[0] ?? '');
        $this->assertStringNotContainsString('Kelurahan', $footer[0] ?? '');
        $this->assertStringNotContainsString('Kabupaten Mimika', $footer[0] ?? '');
        $this->assertSame(1, substr_count($html, 'Inauga'));
        $this->assertSame(1, substr_count($html, 'Kelurahan'));
        $this->assertSame(1, substr_count($html, 'Kabupaten Mimika'));
    }
}
