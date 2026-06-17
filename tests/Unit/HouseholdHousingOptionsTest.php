<?php

namespace Tests\Unit;

use App\Support\HouseholdHousingOptions;
use PHPUnit\Framework\TestCase;

class HouseholdHousingOptionsTest extends TestCase
{
    public function test_normalize_status_maps_legacy_casing(): void
    {
        $this->assertSame('Milik sendiri', HouseholdHousingOptions::normalizeStatus('milik sendiri'));
        $this->assertSame('Kontrak', HouseholdHousingOptions::normalizeStatus('KONTRAK'));
        $this->assertSame('Menumpang', HouseholdHousingOptions::normalizeStatus(' menumpang '));
    }

    public function test_requires_kondisi_only_for_milik_sendiri(): void
    {
        $this->assertTrue(HouseholdHousingOptions::requiresKondisiRumahMilik('Milik sendiri'));
        $this->assertTrue(HouseholdHousingOptions::requiresKondisiRumahMilik('milik sendiri'));
        $this->assertFalse(HouseholdHousingOptions::requiresKondisiRumahMilik('Kontrak'));
        $this->assertFalse(HouseholdHousingOptions::requiresKondisiRumahMilik('Menumpang'));
        $this->assertFalse(HouseholdHousingOptions::requiresKondisiRumahMilik(null));
    }

    public function test_status_and_kondisi_labels(): void
    {
        $this->assertSame('Milik sendiri', HouseholdHousingOptions::statusLabel('milik sendiri'));
        $this->assertSame('Rumah ortu', HouseholdHousingOptions::statusLabel('Rumah ortu'));
        $this->assertSame('Layak', HouseholdHousingOptions::kondisiLabel('layak'));
        $this->assertSame('—', HouseholdHousingOptions::kondisiLabel(null));
    }
}
