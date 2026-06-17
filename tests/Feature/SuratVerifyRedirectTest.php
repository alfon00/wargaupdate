<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratVerifyRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_verifikasi_url_without_intended_service_redirects_to_catalog(): void
    {
        $this->get('/layanan/surat/verifikasi')
            ->assertRedirect('/layanan/surat');
    }
}
