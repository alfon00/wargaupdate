<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\PendataanDocument;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use App\Services\WahaNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class PendataanVerificationRejectTest extends TestCase
{
    use RefreshDatabase;

    private function createRtWithStaff(string $rtNumber = '001'): array
    {
        $profile = RtProfile::create([
            'rt_number' => $rtNumber,
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT '.$rtNumber,
        ]);

        $staff = User::create([
            'name' => 'Ketua RT '.$rtNumber,
            'email' => 'ketua-pendataan-'.$rtNumber.'@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        return [$profile, $staff];
    }

    private function seedPendingPendataan(RtProfile $rt): Resident
    {
        $household = Household::create([
            'rt_profile_id' => $rt->id,
            'family_card_number' => '3201010101010077',
            'address' => 'Jl. Verifikasi No. 1',
            'status' => 'menunggu_verifikasi',
            'pendataan_category' => 'pendataan_ulang',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        $head = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010077',
            'name' => 'Kepala Verifikasi',
            'phone' => '081277788899',
            'birth_place' => 'Timika',
            'birth_date' => '1985-05-05',
            'gender' => 'Laki-laki',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'occupation' => 'Wiraswasta',
            'education' => 'SMA/SMK',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::MenungguVerifikasi,
        ]);

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/'.$household->id.'/kk-test.jpg',
            'original_name' => 'kk-test.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_a0',
            'file_path' => 'pendataan/'.$household->id.'/ktp-test.jpg',
            'original_name' => 'ktp-test.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        return $head;
    }

    /** @return array<string, mixed> */
    private function documentPayload(Resident $head): array
    {
        return [
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'whatsapp_notify' => '1',
            'members' => [[
                'resident_id' => $head->id,
                'document_id' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ]],
        ];
    }

    public function test_rt_reject_restores_active_status_and_sends_whatsapp(): void
    {
        [$rt, $staff] = $this->createRtWithStaff('010');
        $head = $this->seedPendingPendataan($rt);

        $waha = Mockery::mock(WahaNotificationService::class);
        $waha->shouldReceive('notifyPendataanRejected')
            ->once()
            ->withArgs(function ($resident, $rtProfile, $notes) use ($head, $rt) {
                return $resident->id === $head->id
                    && $rtProfile->id === $rt->id
                    && $notes === 'Scan KK tidak jelas';
            });
        $this->app->instance(WahaNotificationService::class, $waha);

        $this->actingAs($staff)
            ->post(route('rt.pendataan.reject', $head), [
                'rejection_notes' => 'Scan KK tidak jelas',
            ])
            ->assertRedirect(route('rt.pendataan.index'))
            ->assertSessionHas('success');

        $head->refresh();
        $this->assertSame(DomicileStatus::Aktif, $head->domicile_status);
        $this->assertSame('Scan KK tidak jelas', $head->verification_notes);
        $this->assertSame('aktif', $head->household->status);
    }

    public function test_rt_approve_sets_active_status(): void
    {
        [$rt, $staff] = $this->createRtWithStaff('011');
        $head = $this->seedPendingPendataan($rt);

        $waha = Mockery::mock(WahaNotificationService::class);
        $waha->shouldReceive('notifyPendataanVerified')->once();
        $this->app->instance(WahaNotificationService::class, $waha);

        $this->actingAs($staff)
            ->post(route('rt.pendataan.approve', $head))
            ->assertRedirect(route('rt.pendataan.index'))
            ->assertSessionHas('success');

        $head->refresh();
        $this->assertSame(DomicileStatus::Aktif, $head->domicile_status);
        $this->assertSame('aktif', $head->household->status);
    }

    public function test_pendataan_show_has_detail_and_update_actions(): void
    {
        [$rt, $staff] = $this->createRtWithStaff('012');
        $head = $this->seedPendingPendataan($rt);

        $this->actingAs($staff)
            ->get(route('rt.pendataan.show', $head))
            ->assertOk()
            ->assertSee('Detail berkas', false)
            ->assertSee('Perbarui data KK', false)
            ->assertSee('Perbarui data', false)
            ->assertSee('Status perkawinan', false)
            ->assertSee('Kewarganegaraan', false)
            ->assertSee('Kawin', false)
            ->assertSee('WNI', false)
            ->assertSee('Tolak pendataan', false);
    }

    public function test_household_edit_returns_to_pendataan_verification(): void
    {
        [$rt, $staff] = $this->createRtWithStaff('013');
        $head = $this->seedPendingPendataan($rt);

        $this->actingAs($staff)
            ->put(route('rt.households.update', $head->household), [
                'return' => 'pendataan',
                'pendataan_head' => $head->id,
                'household_id' => $head->household_id,
                'family_card_number' => '3201010101010077',
                'address' => 'Jl. Verifikasi Baru No. 2',
                'status_rumah_tinggal' => 'Kontrak',
                'suku' => 'Kamoro',
            ])
            ->assertRedirect(route('rt.pendataan.show', $head))
            ->assertSessionHas('success');

        $this->assertSame('Jl. Verifikasi Baru No. 2', $head->household->fresh()->address);
    }
}
