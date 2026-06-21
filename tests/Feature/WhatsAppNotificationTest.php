<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Enums\ReportStatus;
use App\Models\Application;
use App\Models\CitizenReport;
use App\Models\GeneratedLetter;
use App\Models\Household;
use App\Models\LetterTemplate;
use App\Models\NotificationLog;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use App\Support\SuratPengantarTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
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
    }

    private function fakeWahaWorking(): void
    {
        Http::fake([
            'http://waha:3000/api/sessions/default' => Http::response([
                'name' => 'default',
                'status' => 'WORKING',
            ]),
            'http://waha:3000/api/sendText' => Http::response(['id' => 'wa-msg-1'], 200),
        ]);
    }

    /** @return array{0: User, 1: Application} */
    private function seedApplication(string $number = 'RT008-2026060101'): array
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
            'email' => 'wa-test-'.$number.'@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_wa_'.$number,
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
            'nik' => '3201010101010'.substr($number, -3),
            'name' => 'Warga WA Test',
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

        return [$staff, $application];
    }

    public function test_observer_sends_submitted_notification_on_create(): void
    {
        $this->fakeWahaWorking();

        [, $application] = $this->seedApplication('RT008-2026060102');

        $log = NotificationLog::query()
            ->where('application_id', $application->id)
            ->where('event', 'submitted')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertStringContainsString($application->application_number, $log->message);
    }

    public function test_verify_sends_verified_notification(): void
    {
        $this->fakeWahaWorking();

        [$staff, $application] = $this->seedApplication('RT008-2026060104');

        $this->actingAs($staff)
            ->post(route('rt.applications.verify', $application));

        $log = NotificationLog::query()
            ->where('application_id', $application->id)
            ->where('event', 'verified')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
    }

    public function test_mark_ready_does_not_auto_send_approved_whatsapp(): void
    {
        $this->fakeWahaWorking();
        Storage::fake('local');

        [$staff, $application] = $this->seedApplication('RT008-2026060105');
        $application->update(['status' => ApplicationStatus::VerifikasiRt]);

        $path = 'letters/'.$application->id.'/test.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4');

        GeneratedLetter::create([
            'application_id' => $application->id,
            'letter_number' => '001/RT008/06/2026',
            'file_path' => $path,
            'signed_at' => now(),
            'signature_path' => 'signatures/test.png',
        ]);

        $this->actingAs($staff)
            ->post(route('rt.applications.mark-ready', $application))
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertSame(ApplicationStatus::SiapDiambil, $application->status);

        $log = NotificationLog::query()
            ->where('application_id', $application->id)
            ->where('event', 'approved')
            ->latest()
            ->first();

        $this->assertNull($log);
    }

    public function test_reject_sends_template_notification_with_application_number(): void
    {
        $this->fakeWahaWorking();

        [$staff, $application] = $this->seedApplication('RT008-2026060106');
        $applicationId = $application->id;
        $residentId = $application->resident_id;
        $notes = 'Berkas tidak lengkap dan tidak dapat diproses.';

        $this->actingAs($staff)
            ->post(route('rt.applications.reject', $application), [
                'rejection_message' => $notes,
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
        $this->assertStringContainsString('RT008-2026060106', $log->message);
        $this->assertStringContainsString($notes, $log->message);
    }

    public function test_show_page_displays_notification_log_section(): void
    {
        $this->fakeWahaWorking();

        [$staff, $application] = $this->seedApplication('RT008-2026060108');

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('Notifikasi WhatsApp', false)
            ->assertSee('Permohonan diajukan', false);
    }

    public function test_pendataan_submitted_sends_whatsapp_on_document_upload(): void
    {
        $this->fakeWahaWorking();

        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Pendataan WA',
            'status' => 'aktif',
        ]);

        $head = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Pendataan WA',
            'phone' => '081234567811',
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $profile->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ])->assertRedirect(route('services.pendataan-ulang'));

        $this->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->post(route('services.pendataan-ulang.store'), [
                'whatsapp_notify' => '1',
                'members' => [[
                    'resident_id' => $head->id,
                    'document_id' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
                ]],
                'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('services.pendataan-ulang.success'));

        $log = NotificationLog::query()
            ->where('resident_id', $head->id)
            ->where('event', 'pendataan_submitted')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
    }

    public function test_report_submitted_sends_whatsapp_confirmation(): void
    {
        $this->fakeWahaWorking();

        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $this->post(route('contact.store'), [
            'rt_profile_id' => $profile->id,
            'category' => 'umum',
            'reporter_name' => 'Pelapor WA',
            'phone' => '081234567812',
            'message' => 'Mohon info jam layanan RT.',
            'declaration' => '1',
        ])->assertRedirect(route('contact.success'));

        $report = CitizenReport::first();
        $this->assertNotNull($report);
        $this->assertStringContainsString('Pertanyaan umum', $report->subject);

        $log = NotificationLog::query()
            ->where('citizen_report_id', $report->id)
            ->where('event', 'report_submitted')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertStringContainsString($report->report_number, $log->message);
    }

    public function test_report_status_update_sends_whatsapp(): void
    {
        $this->fakeWahaWorking();

        [$staff] = $this->seedApplication('RT008-2026060109');

        $profile = RtProfile::where('rt_number', '008')->first();
        $report = CitizenReport::create([
            'report_number' => CitizenReport::generateNumber($profile->rt_number),
            'rt_profile_id' => $profile->id,
            'category' => 'umum',
            'reporter_name' => 'Pelapor Status',
            'phone' => '081234567813',
            'subject' => 'Kendala portal',
            'message' => 'Tidak bisa login.',
            'status' => ReportStatus::Baru,
        ]);

        $this->actingAs($staff)
            ->post(route('rt.reports.status', $report), [
                'status' => ReportStatus::Selesai->value,
                'response_note' => 'Sudah ditangani.',
            ])
            ->assertRedirect();

        $log = NotificationLog::query()
            ->where('citizen_report_id', $report->id)
            ->where('event', 'report_status_updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertStringContainsString('Selesai', $log->message);
    }

    public function test_pendataan_show_displays_notification_log_section(): void
    {
        $this->fakeWahaWorking();

        [$staff, $application] = $this->seedApplication('RT008-2026060110');
        $head = $application->resident;
        $head->update(['domicile_status' => DomicileStatus::MenungguVerifikasi, 'is_head_of_family' => true]);

        NotificationLog::create([
            'resident_id' => $head->id,
            'phone' => $head->phone,
            'event' => 'pendataan_submitted',
            'message' => 'Test pendataan submitted',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.pendataan.show', $head))
            ->assertOk()
            ->assertSee('Notifikasi WhatsApp', false)
            ->assertSee('Pengajuan diterima', false);
    }

    public function test_non_head_member_application_uses_household_phone_for_whatsapp(): void
    {
        $this->fakeWahaWorking();

        $profile = RtProfile::create([
            'rt_number' => '009',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 009',
        ]);

        $service = ServiceType::create([
            'code' => 'surat_member',
            'name' => 'Surat Anggota',
            'is_active' => true,
        ]);

        LetterTemplate::create([
            'service_type_id' => $service->id,
            'name' => 'Template Anggota',
            'body_html' => SuratPengantarTemplate::bodyHtml(),
            'is_active' => true,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Keluarga',
            'pendataan_category' => 'warga_baru',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010098',
            'name' => 'Kepala Keluarga',
            'phone' => '081234567801',
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Anggota Keluarga',
            'phone' => null,
            'whatsapp_notify' => true,
            'is_head_of_family' => false,
            'relationship_to_head' => 'Anak',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $application = Application::create([
            'application_number' => 'RT009-2026060199',
            'service_type_id' => $service->id,
            'resident_id' => $member->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::Diajukan,
            'purpose' => 'Keperluan sekolah',
            'submitted_at' => now(),
        ]);

        $log = NotificationLog::query()
            ->where('application_id', $application->id)
            ->where('event', 'submitted')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertSame('081234567801', $log->phone);
    }

    public function test_report_show_displays_notification_log_section(): void
    {
        $this->fakeWahaWorking();

        [$staff] = $this->seedApplication('RT008-2026060111');
        $profile = RtProfile::where('rt_number', '008')->first();

        $report = CitizenReport::create([
            'report_number' => CitizenReport::generateNumber($profile->rt_number),
            'rt_profile_id' => $profile->id,
            'category' => 'umum',
            'reporter_name' => 'Pelapor UI',
            'phone' => '081234567814',
            'subject' => 'Uji tampilan log',
            'message' => 'Pesan uji.',
            'status' => ReportStatus::Baru,
        ]);

        NotificationLog::create([
            'citizen_report_id' => $report->id,
            'phone' => $report->phone,
            'event' => 'report_submitted',
            'message' => 'Test report submitted',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.reports.show', $report))
            ->assertOk()
            ->assertSee('Notifikasi WhatsApp', false)
            ->assertSee('Laporan diterima', false)
            ->assertDontSee('Kirim WhatsApp ke pelapor', false);
    }
}
