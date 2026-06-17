<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\RtResidentDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RtResidentDeletionTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: RtProfile, 2: Household} */
    private function seedRtWithHousehold(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-delete@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Merpati',
            'pendataan_category' => '',
        ]);

        return [$staff, $profile, $household];
    }

    private function sampleSignatureDataUri(): string
    {
        $image = imagecreatetruecolor(200, 80);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        imagefilledellipse($image, 100, 40, 120, 40, $black);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png ?: '');
    }

    public function test_can_delete_resident_without_application(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Anggota Hapus',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Kepala KK',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.residents.destroy', $member), [
                'filter' => 'semua',
                'kategori' => 'semua',
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.residents.show', [
                'resident' => $member->id,
                'household' => $household->id,
                'filter' => 'semua',
                'kategori' => 'semua',
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('residents', ['id' => $member->id]);
        $this->assertDatabaseHas('permanent_deletion_requests', [
            'resident_id' => $member->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_delete_resident_with_application(): void
    {
        [$staff, $profile, $household] = $this->seedRtWithHousehold();

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Surat',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $service = ServiceType::create([
            'code' => 'skdp',
            'name' => 'SKDP',
            'is_active' => true,
        ]);

        Application::create([
            'application_number' => 'RT008-2026060001',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.residents.destroy', $resident), [
                'filter' => 'semua',
                'kategori' => 'semua',
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.residents.show', [
                'resident' => $resident->id,
                'household' => $household->id,
                'filter' => 'semua',
                'kategori' => 'semua',
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('residents', ['id' => $resident->id]);
        $this->assertDatabaseHas('permanent_deletion_requests', [
            'resident_id' => $resident->id,
            'status' => 'pending',
        ]);
    }

    public function test_resident_show_member_table_has_detail_only(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Kepala Zona',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Anggota Zona',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.residents.show', [
                'resident' => $member,
                'household' => $household->id,
            ]))
            ->assertOk()
            ->assertDontSee('Kelola data warga', false)
            ->assertSee('Daftar Anggota Keluarga', false)
            ->assertSee('Detail', false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $member).'"', false);
    }

    public function test_resident_edit_allows_delete_when_has_application(): void
    {
        [$staff, $profile, $household] = $this->seedRtWithHousehold();

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Surat UI',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $service = ServiceType::create([
            'code' => 'skdp-ui',
            'name' => 'SKDP UI',
            'is_active' => true,
        ]);

        Application::create([
            'application_number' => 'RT008-2026060099',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', $resident))
            ->assertOk()
            ->assertSee('data-delete-action="'.route('rt.residents.destroy', $resident).'"', false);
    }

    public function test_resident_show_archived_has_no_table_delete(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $archived = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010097',
            'name' => 'Arsip Zona',
            'domicile_status' => DomicileStatus::PindahKeluar,
            'is_head_of_family' => true,
            'departure_reason' => 'pindah',
            'departed_at' => now(),
            'departed_by' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.residents.show', [
                'resident' => $archived,
                'filter' => 'arsip',
                'household' => $household->id,
            ]))
            ->assertOk()
            ->assertDontSee('Kelola data warga', false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $archived).'"', false);
    }

    public function test_can_delete_archived_resident_with_application(): void
    {
        [$staff, $profile, $household] = $this->seedRtWithHousehold();

        $archived = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010096',
            'name' => 'Arsip Surat',
            'domicile_status' => DomicileStatus::PindahKeluar,
            'is_head_of_family' => true,
            'departure_reason' => 'pindah',
            'departed_at' => now(),
            'departed_by' => $staff->id,
        ]);

        $service = ServiceType::create([
            'code' => 'skdp-arsip',
            'name' => 'SKDP Arsip',
            'is_active' => true,
        ]);

        Application::create([
            'application_number' => 'RT008-2026060096',
            'service_type_id' => $service->id,
            'resident_id' => $archived->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::Disetujui,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.residents.destroy', $archived), [
                'filter' => 'arsip',
                'household' => $household->id,
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.residents.show', [
                'resident' => $archived->id,
                'household' => $household->id,
                'filter' => 'arsip',
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('permanent_deletion_requests', [
            'resident_id' => $archived->id,
            'status' => 'pending',
        ]);
    }

    public function test_destroy_validation_error_redirects_back_to_resident_show(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $archived = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010095',
            'name' => 'Arsip Error',
            'domicile_status' => DomicileStatus::Meninggal,
            'is_head_of_family' => true,
            'departure_reason' => 'meninggal',
            'departed_at' => now(),
            'departed_by' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.residents.destroy', $archived), [
                'filter' => 'arsip',
                'household' => $household->id,
            ])
            ->assertRedirect(route('rt.residents.show', [
                'resident' => $archived->id,
                'household' => $household->id,
                'filter' => 'arsip',
            ]))
            ->assertSessionHasErrors('signature_data');
    }

    public function test_can_delete_empty_household(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $this->actingAs($staff)
            ->delete(route('rt.households.destroy', $household), [
                'filter' => 'semua',
                'kategori' => 'semua',
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.data-warga.index', [
                'filter' => 'semua',
                'kategori' => 'semua',
                'household' => $household->id,
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('households', ['id' => $household->id]);
        $this->assertDatabaseHas('permanent_deletion_requests', [
            'household_id' => $household->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_delete_household_when_all_members_deletable(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Satu Anggota',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.households.destroy', $household), [
                'filter' => 'semua',
                'kategori' => 'semua',
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.data-warga.index', [
                'filter' => 'semua',
                'kategori' => 'semua',
                'household' => $household->id,
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('households', ['id' => $household->id]);
        $this->assertDatabaseHas('permanent_deletion_requests', [
            'household_id' => $household->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('residents', ['household_id' => $household->id]);
    }

    public function test_data_warga_index_does_not_show_delete_actions(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Kepala Hapus',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Anggota Hapus',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', [
                'filter' => 'semua',
                'kategori' => 'semua',
                'q' => 'Anggota Hapus',
            ]))
            ->assertOk()
            ->assertSee('Menampilkan:', false)
            ->assertSee('lw-rt-data-row-actions', false)
            ->assertSee('Detail', false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $member).'"', false);
    }

    public function test_resident_edit_shows_danger_zone(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Anggota Zona',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Kepala Zona',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', $member))
            ->assertOk()
            ->assertSee('Zona berbahaya', false)
            ->assertSee('Hapus warga permanen', false)
            ->assertSee('lw-rt-delete-trigger', false)
            ->assertSee('data-delete-action', false);
    }

    public function test_cannot_delete_resident_without_signature(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Tanpa TTD',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Kepala Tanpa TTD',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.residents.destroy', $member), [
                'filter' => 'semua',
            ])
            ->assertSessionHasErrors('signature_data');

        $this->assertDatabaseHas('residents', ['id' => $member->id]);
    }

    public function test_cannot_delete_resident_with_blank_signature(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010096',
            'name' => 'TTD Kosong',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010095',
            'name' => 'Kepala TTD Kosong',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.residents.destroy', $member), [
                'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            ])
            ->assertSessionHasErrors('signature_data');

        $this->assertDatabaseHas('residents', ['id' => $member->id]);
    }

    public function test_delete_last_resident_removes_empty_household(): void
    {
        [, , $household] = $this->seedRtWithHousehold();

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Satu-satunya Anggota',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        app(RtResidentDeletionService::class)->deleteResident($resident);

        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
        $this->assertDatabaseMissing('households', ['id' => $household->id]);
    }
}
