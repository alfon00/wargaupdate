<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\PermanentDeletionRequestStatus;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\PermanentDeletionRequest;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use App\Services\PermanentDeletionRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermanentDeletionRequestTest extends TestCase
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
            'email' => 'ketua-delete-req@test.local',
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

    private function createSuperAdmin(): User
    {
        return User::create([
            'name' => 'Admin Sistem',
            'email' => 'super-admin-delete-req@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);
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

    public function test_rt_submit_resident_deletion_request_instead_of_direct_delete(): void
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
            'status' => PermanentDeletionRequestStatus::Pending->value,
        ]);
    }

    public function test_admin_approve_deletes_resident(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();
        $admin = $this->createSuperAdmin();

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

        $request = app(PermanentDeletionRequestService::class)->submitResident(
            request()->merge(['signature_data' => $this->sampleSignatureDataUri()]),
            $member,
            $staff,
        );

        $this->actingAs($admin)
            ->post(route('admin.deletion-requests.approve', $request))
            ->assertRedirect(route('admin.deletion-requests.index', ['status' => 'pending']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('residents', ['id' => $member->id]);
        $this->assertSame(
            PermanentDeletionRequestStatus::Approved,
            $request->fresh()->status,
        );
    }

    public function test_admin_reject_keeps_resident(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();
        $admin = $this->createSuperAdmin();

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

        $request = app(PermanentDeletionRequestService::class)->submitResident(
            request()->merge(['signature_data' => $this->sampleSignatureDataUri()]),
            $member,
            $staff,
        );

        $this->actingAs($admin)
            ->post(route('admin.deletion-requests.reject', $request), [
                'admin_notes' => 'Data masih diperlukan untuk audit.',
            ])
            ->assertRedirect(route('admin.deletion-requests.show', $request))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('residents', ['id' => $member->id]);
        $request->refresh();
        $this->assertSame(PermanentDeletionRequestStatus::Rejected, $request->status);
        $this->assertSame('Data masih diperlukan untuk audit.', $request->admin_notes);
    }

    public function test_duplicate_pending_request_is_blocked(): void
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

        PermanentDeletionRequest::create([
            'request_number' => 'DEL-RT008-202606090001',
            'rt_profile_id' => $household->rt_profile_id,
            'requested_by' => $staff->id,
            'target_type' => 'resident',
            'resident_id' => $member->id,
            'household_id' => $household->id,
            'target_name' => $member->name,
            'target_nik' => $member->nik,
            'family_card_number' => $household->family_card_number,
            'signature_path' => 'signatures/test.png',
            'status' => PermanentDeletionRequestStatus::Pending,
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.residents.destroy', $member), [
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertSessionHasErrors('delete');
    }

    public function test_resident_show_displays_pending_deletion_banner(): void
    {
        [$staff, , $household] = $this->seedRtWithHousehold();

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Anggota Hapus',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        PermanentDeletionRequest::create([
            'request_number' => 'DEL-RT008-202606090002',
            'rt_profile_id' => $household->rt_profile_id,
            'requested_by' => $staff->id,
            'target_type' => 'resident',
            'resident_id' => $member->id,
            'household_id' => $household->id,
            'target_name' => $member->name,
            'target_nik' => $member->nik,
            'family_card_number' => $household->family_card_number,
            'signature_path' => 'signatures/test.png',
            'status' => PermanentDeletionRequestStatus::Pending,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.residents.show', $member))
            ->assertOk()
            ->assertSee('menunggu persetujuan admin sistem', false)
            ->assertSee('Pengajuan hapus permanen sedang menunggu persetujuan admin sistem.', false);
    }

    public function test_admin_deletion_index_defaults_to_pending_and_supports_filters(): void
    {
        [$staff, $profile, $household] = $this->seedRtWithHousehold();
        $admin = $this->createSuperAdmin();

        PermanentDeletionRequest::create([
            'request_number' => 'DEL-RT008-202606100001',
            'rt_profile_id' => $profile->id,
            'requested_by' => $staff->id,
            'target_type' => 'resident',
            'resident_id' => null,
            'household_id' => $household->id,
            'target_name' => 'Warga Menunggu',
            'target_nik' => '3201010101010002',
            'family_card_number' => $household->family_card_number,
            'signature_path' => 'signatures/pending.png',
            'status' => PermanentDeletionRequestStatus::Pending,
        ]);

        PermanentDeletionRequest::create([
            'request_number' => 'DEL-RT008-202606100002',
            'rt_profile_id' => $profile->id,
            'requested_by' => $staff->id,
            'target_type' => 'resident',
            'resident_id' => null,
            'household_id' => $household->id,
            'target_name' => 'Warga Sudah Dihapus',
            'target_nik' => '3201010101010003',
            'family_card_number' => $household->family_card_number,
            'signature_path' => 'signatures/approved.png',
            'status' => PermanentDeletionRequestStatus::Approved,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.deletion-requests.index'))
            ->assertOk()
            ->assertSee('Warga Menunggu', false)
            ->assertDontSee('Warga Sudah Dihapus', false)
            ->assertSee('Review', false)
            ->assertDontSee('>Detail<', false);

        $this->actingAs($admin)
            ->get(route('admin.deletion-requests.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Warga Sudah Dihapus', false)
            ->assertDontSee('Warga Menunggu', false)
            ->assertSee('Detail', false);

        $this->actingAs($admin)
            ->get(route('admin.deletion-requests.index', ['status' => 'all']))
            ->assertOk()
            ->assertSee('Warga Menunggu', false)
            ->assertSee('Warga Sudah Dihapus', false);

        $this->actingAs($admin)
            ->get(route('admin.deletion-requests.show', PermanentDeletionRequest::where('request_number', 'DEL-RT008-202606100002')->first()))
            ->assertOk()
            ->assertSee('Data target telah dihapus permanen', false)
            ->assertSee('snapshot saat pengajuan', false);
    }
}
