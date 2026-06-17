<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\PermanentDeletionRequestStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\PermanentDeletionRequestService;
use App\Services\RtResidentDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicationOrphanTest extends TestCase
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
            'email' => 'ketua-orphan@test.local',
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

    private function createServiceType(): ServiceType
    {
        return ServiceType::create([
            'code' => 'skdp-orphan',
            'name' => 'SKDP Orphan',
            'is_active' => true,
        ]);
    }

    public function test_rt_applications_index_handles_orphan_without_snapshot(): void
    {
        [$staff, $profile] = $this->seedRtWithHousehold();
        $service = $this->createServiceType();

        Application::create([
            'application_number' => 'RT008-2026060100',
            'service_type_id' => $service->id,
            'resident_id' => null,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.applications.index'))
            ->assertOk()
            ->assertSee('Warga (data dihapus)', false);
    }

    public function test_rt_applications_index_shows_archived_applicant_snapshot(): void
    {
        [$staff, $profile] = $this->seedRtWithHousehold();
        $service = $this->createServiceType();

        Application::create([
            'application_number' => 'RT008-2026060101',
            'service_type_id' => $service->id,
            'resident_id' => null,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'form_data' => [
                'archived_applicant' => [
                    'name' => 'Budi Arsip',
                    'nik' => '3201010101010002',
                    'rt_label' => 'RT 008',
                ],
            ],
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.applications.index'))
            ->assertOk()
            ->assertSee('Budi Arsip', false);
    }

    public function test_rt_applications_search_finds_orphan_by_archived_name(): void
    {
        [$staff, $profile] = $this->seedRtWithHousehold();
        $service = $this->createServiceType();

        Application::create([
            'application_number' => 'RT008-2026060102',
            'service_type_id' => $service->id,
            'resident_id' => null,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'form_data' => [
                'archived_applicant' => [
                    'name' => 'Siti Terarsip',
                ],
            ],
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.applications.index', ['q' => 'Siti Terarsip']))
            ->assertOk()
            ->assertSee('RT008-2026060102', false)
            ->assertSee('Siti Terarsip', false);
    }

    public function test_delete_resident_deletes_applications(): void
    {
        [, $profile, $household] = $this->seedRtWithHousehold();

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Snapshot',
            'phone' => '081234567890',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $service = $this->createServiceType();

        $application = Application::create([
            'application_number' => 'RT008-2026060103',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        app(RtResidentDeletionService::class)->deleteResident($resident);

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
    }

    public function test_admin_approve_deletion_removes_applications(): void
    {
        [$staff, $profile, $household] = $this->seedRtWithHousehold();
        $admin = User::create([
            'name' => 'Admin Sistem',
            'email' => 'super-admin-orphan@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Warga Hapus Admin',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $service = $this->createServiceType();

        $application = Application::create([
            'application_number' => 'RT008-2026060104',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::Disetujui,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $image = imagecreatetruecolor(200, 80);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        imagefilledellipse($image, 100, 40, 120, 40, $black);
        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);
        $signature = 'data:image/png;base64,'.base64_encode($png ?: '');

        $request = app(PermanentDeletionRequestService::class)->submitResident(
            request()->merge(['signature_data' => $signature]),
            $resident,
            $staff,
        );

        $this->actingAs($admin)
            ->post(route('admin.deletion-requests.approve', $request))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        $this->assertSame(PermanentDeletionRequestStatus::Approved, $request->fresh()->status);

        $this->actingAs($staff)
            ->get(route('rt.applications.index'))
            ->assertOk()
            ->assertDontSee('RT008-2026060104', false)
            ->assertDontSee('Warga Hapus Admin', false);
    }
}
