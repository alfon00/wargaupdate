<?php

namespace Tests\Feature;

use App\Models\ServiceType;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sktm_show_page_displays_kk_and_ktp_requirements(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $service = ServiceType::query()->where('code', 'surat_tidak_mampu')->firstOrFail();

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSeeInOrder(['>KK<', '>KTP<'], false)
            ->assertDontSee('Surat permohonan', false)
            ->assertDontSee('KTP dan KK warga terdata', false)
            ->assertDontSee('Data pendukung keperluan SKTM', false);
    }

    public function test_domisili_show_page_displays_kk_and_ktp_only(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $service = ServiceType::query()->where('code', 'surat_domisili')->firstOrFail();

        $response = $this->get(route('services.show', $service));

        $response->assertOk()
            ->assertSeeInOrder(['>KK<', '>KTP<'], false)
            ->assertDontSee('Fotokopi KTP', false)
            ->assertDontSee('KTP dan KK warga terdata', false);

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, '>KK<'));
        $this->assertSame(1, substr_count($html, '>KTP<'));
    }
}
