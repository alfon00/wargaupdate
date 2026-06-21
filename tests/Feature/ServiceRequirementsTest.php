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

    public function test_surat_catalog_uses_keterangan_labels_not_pengantar(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $response = $this->get(route('services.surat'));
        $response->assertOk();

        foreach (ServiceCatalogSeeder::catalog() as $svc) {
            $response->assertSee($svc['name'], false);
            $response->assertSee($svc['description'], false);
        }

        $html = $response->getContent();
        preg_match_all('/class="lw-service-card-name"[^>]*>([^<]+)</', $html, $names);
        preg_match_all('/class="lw-service-card-desc"[^>]*>([^<]+)</', $html, $descriptions);

        foreach (array_merge($names[1] ?? [], $descriptions[1] ?? []) as $text) {
            $this->assertStringNotContainsStringIgnoringCase('pengantar', $text);
        }
    }
}
