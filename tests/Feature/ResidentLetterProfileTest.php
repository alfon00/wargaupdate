<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Support\ResidentLetterProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentLetterProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_resident_has_no_missing_keys(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Lengkap',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Amungme',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010008',
            'name' => 'Warga Lengkap',
            'gender' => 'Laki-laki',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'religion' => 'Islam',
            'occupation' => 'Karyawan',
            'education' => 'SMA',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->assertTrue(ResidentLetterProfile::isComplete($resident));
        $this->assertSame([], ResidentLetterProfile::missingKeys($resident));
    }

    public function test_incomplete_resident_lists_missing_education(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'kelurahan' => 'Kelurahan Inauga',
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Test',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010009',
            'name' => 'Warga Kurang',
            'gender' => 'Perempuan',
            'birth_place' => 'Timika',
            'birth_date' => '1995-05-05',
            'religion' => 'Islam',
            'occupation' => 'IRT',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->assertFalse(ResidentLetterProfile::isComplete($resident));
        $this->assertContains('education', ResidentLetterProfile::missingKeys($resident));
    }
}
