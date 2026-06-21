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
use App\Models\NotificationLog;
use App\Support\ApplicationRejectionMessage;
use Illuminate\Support\Facades\Http;
use App\Support\SuratPengantarTemplate;
use App\Models\LetterTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RtApplicationReviewActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'waha.api_key' => 'test-waha-key',
            'waha.base_url' => 'http://waha:3000',
            'waha.session' => 'default',
        ]);

        Http::fake([
            'http://waha:3000/api/sessions/default' => Http::response([
                'name' => 'default',
                'status' => 'WORKING',
            ]),
            'http://waha:3000/api/sendText' => Http::response(['id' => 'wa-msg-1'], 200),
        ]);
    }

    /** @return array{0: User, 1: RtProfile, 2: Application} */
    private function seedReviewableApplication(string $number = 'RT008-2026060001'): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
            'ketua_rw' => 'Ketua RW 005',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008-review@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_review_'.$number,
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        LetterTemplate::create([
            'service_type_id' => $service->id,
            'name' => 'Template Domisili',
            'body_html' => SuratPengantarTemplate::bodyHtml(),
            'is_active' => true,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Test',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Review',
            'phone' => '081234567899',
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $application = Application::create([
            'application_number' => $number,
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::Diajukan,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        return [$staff, $profile, $application];
    }

    public function test_verify_redirects_to_compose_and_updates_status(): void
    {
        [$staff, , $application] = $this->seedReviewableApplication();

        $this->actingAs($staff)
            ->post(route('rt.applications.verify', $application))
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertSame(ApplicationStatus::VerifikasiRt, $application->status);
    }

    public function test_reject_deletes_application_and_sends_whatsapp(): void
    {
        [$staff, , $application] = $this->seedReviewableApplication();
        $applicationId = $application->id;
        $message = 'Pesan penolakan kustom dari RT.';

        $this->actingAs($staff)
            ->post(route('rt.applications.reject', $application), [
                'rejection_message' => $message,
            ])
            ->assertRedirect(route('rt.applications.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('applications', ['id' => $applicationId]);

        $log = NotificationLog::query()
            ->where('resident_id', $application->resident_id)
            ->where('event', 'application_rejected')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertStringContainsString($application->application_number, $log->message);
        $this->assertStringContainsString($message, $log->message);
    }

    public function test_reject_requires_message(): void
    {
        [$staff, , $application] = $this->seedReviewableApplication();

        $this->actingAs($staff)
            ->from(route('rt.applications.show', $application))
            ->post(route('rt.applications.reject', $application), [])
            ->assertRedirect(route('rt.applications.show', $application))
            ->assertSessionHasErrors('rejection_message');

        $this->assertDatabaseHas('applications', ['id' => $application->id]);
    }

    public function test_show_displays_terima_tolak_when_reviewable(): void
    {
        [$staff, , $application] = $this->seedReviewableApplication();

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('lw-rt-application-detail', false)
            ->assertSee('Data pemohon', false)
            ->assertSee('Surat Domisili', false)
            ->assertSee('Terima — lanjut susun surat', false)
            ->assertDontSee('Lengkapi berkas', false)
            ->assertSee('Tolak permohonan', false)
            ->assertDontSee('Catat nomor surat &amp; kirim notifikasi', false)
            ->assertSee(ApplicationRejectionMessage::template($application), false)
            ->assertDontSee('Ubah status manual', false)
            ->assertDontSee('Opsi lanjutan', false);
    }

    public function test_show_verifikasi_rt_shows_tolak_not_terima(): void
    {
        [$staff, , $application] = $this->seedReviewableApplication('RT008-2026060099');
        $application->update(['status' => ApplicationStatus::VerifikasiRt]);

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('Keputusan permohonan', false)
            ->assertDontSee('Lengkapi berkas', false)
            ->assertSee('Tolak permohonan', false)
            ->assertDontSee('Terima — lanjut susun surat', false)
            ->assertSee('Surat pengantar RT', false)
            ->assertSee('Susun &amp; terbitkan surat', false)
            ->assertDontSee('Zona berbahaya', false)
            ->assertDontSee('Tandai siap diambil', false);
    }

    public function test_reject_works_on_verifikasi_rt(): void
    {
        [$staff, , $application] = $this->seedReviewableApplication('RT008-2026060098');
        $application->update(['status' => ApplicationStatus::VerifikasiRt]);
        $applicationId = $application->id;
        $residentId = $application->resident_id;
        $message = 'Permohonan dibatalkan setelah verifikasi.';

        $this->actingAs($staff)
            ->post(route('rt.applications.reject', $application), [
                'rejection_message' => $message,
            ])
            ->assertRedirect(route('rt.applications.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('applications', ['id' => $applicationId]);

        $log = NotificationLog::query()
            ->where('resident_id', $residentId)
            ->where('event', 'application_rejected')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertStringContainsString($message, $log->message);
    }

    public function test_show_hides_review_actions_when_terminal_status(): void
    {
        [$staff, , $application] = $this->seedReviewableApplication('RT008-2026060097');
        $application->update(['status' => ApplicationStatus::SiapDiambil]);

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertDontSee('Terima — lanjut susun surat', false)
            ->assertDontSee('data-rt-reject-open', false)
            ->assertDontSee('data-rt-completion-open', false)
            ->assertDontSee('Keputusan permohonan', false);
    }
}
