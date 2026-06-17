<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\GeneratedLetter;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtApplicationDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: RtProfile, 2: Household, 3: Resident} */
    private function seedRtApplication(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-app-delete@test.local',
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

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Hapus App',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        return [$staff, $profile, $household, $resident];
    }

    private function seedApplication(RtProfile $profile, Resident $resident, string $number = 'RT008-2026060200'): Application
    {
        $service = ServiceType::create([
            'code' => 'skdp-del-'.$number,
            'name' => 'SKDP Delete',
            'is_active' => true,
        ]);

        return Application::create([
            'application_number' => $number,
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::Diajukan,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);
    }

    public function test_rt_owner_can_delete_application(): void
    {
        Storage::fake('local');
        [$staff, $profile, , $resident] = $this->seedRtApplication();
        $application = $this->seedApplication($profile, $resident);

        $docPath = 'applications/'.$application->id.'/ktp.pdf';
        Storage::disk('local')->put($docPath, 'pdf-content');
        ApplicationDocument::create([
            'application_id' => $application->id,
            'document_type' => 'req_0',
            'file_path' => $docPath,
            'original_name' => 'ktp.pdf',
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.applications.destroy', $application))
            ->assertRedirect(route('rt.applications.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('application_documents', ['application_id' => $application->id]);
        Storage::disk('local')->assertMissing($docPath);
    }

    public function test_other_rt_cannot_delete_application(): void
    {
        [, $profile, , $resident] = $this->seedRtApplication();
        $application = $this->seedApplication($profile, $resident);

        $otherProfile = RtProfile::create([
            'rt_number' => '009',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 9',
        ]);

        $otherStaff = User::create([
            'name' => 'Ketua RT 9',
            'email' => 'ketua-rt9-delete@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($otherStaff)
            ->delete(route('rt.applications.destroy', $application))
            ->assertNotFound();

        $this->assertDatabaseHas('applications', ['id' => $application->id]);
    }

    public function test_delete_application_removes_generated_letter_files(): void
    {
        Storage::fake('local');
        [$staff, $profile, , $resident] = $this->seedRtApplication();
        $application = $this->seedApplication($profile, $resident, 'RT008-2026060201');

        $letterPath = 'letters/'.$application->id.'/surat.pdf';
        $signaturePath = 'signatures/'.$application->id.'/ttd.png';
        Storage::disk('local')->put($letterPath, 'letter-pdf');
        Storage::disk('local')->put($signaturePath, 'signature-png');

        GeneratedLetter::create([
            'application_id' => $application->id,
            'file_path' => $letterPath,
            'signature_path' => $signaturePath,
            'letter_number' => '001/SKDP/2026',
            'issued_at' => now(),
        ]);

        $this->actingAs($staff)
            ->delete(route('rt.applications.destroy', $application))
            ->assertRedirect(route('rt.applications.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('generated_letters', ['application_id' => $application->id]);
        Storage::disk('local')->assertMissing($letterPath);
        Storage::disk('local')->assertMissing($signaturePath);
    }

    public function test_show_page_no_longer_has_delete_action(): void
    {
        [$staff, $profile, , $resident] = $this->seedRtApplication();
        $application = $this->seedApplication($profile, $resident, 'RT008-2026060202');

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertDontSee('Hapus permohonan', false)
            ->assertDontSee('Zona berbahaya', false)
            ->assertDontSee('rt-instant-delete-title', false);
    }
}
