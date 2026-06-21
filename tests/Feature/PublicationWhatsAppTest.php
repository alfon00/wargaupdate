<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\RtPublicationType;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\NotificationLog;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\RtPublication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicationWhatsAppTest extends TestCase
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
            'http://waha:3000/api/sendText' => Http::response(['id' => 'wa-msg-pub'], 200),
        ]);
    }

    /** @return array{0: RtProfile, 1: User, 2: RtPublication} */
    private function seedPublication(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'pub-wa@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010201',
            'address' => 'Jl. Broadcast 1',
            'status' => 'aktif',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010201',
            'name' => 'Warga Notif Aktif',
            'phone' => '081234567820',
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $household2 = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010202',
            'address' => 'Jl. Broadcast 2',
            'status' => 'aktif',
        ]);

        Resident::create([
            'household_id' => $household2->id,
            'nik' => '3201010101010202',
            'name' => 'Warga Tanpa HP',
            'phone' => null,
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $publication = RtPublication::create([
            'rt_profile_id' => $profile->id,
            'type' => RtPublicationType::Pengumuman,
            'judul' => 'Pengumuman Rapat RT',
            'ringkasan' => 'Rapat koordinasi warga minggu depan.',
            'tanggal' => now()->toDateString(),
            'is_published' => true,
            'published_at' => now(),
        ]);

        return [$profile, $staff, $publication];
    }

    public function test_publication_broadcast_sends_whatsapp_to_eligible_residents(): void
    {
        $this->fakeWahaWorking();

        [, $staff, $publication] = $this->seedPublication();

        $this->actingAs($staff)
            ->post(route('rt.pengumuman.whatsapp', $publication), [
                'recipient_mode' => 'all',
            ])
            ->assertRedirect(route('rt.pengumuman.edit', $publication))
            ->assertSessionHas('success');

        $logs = NotificationLog::query()
            ->forPublication($publication->id)
            ->get();

        $this->assertCount(1, $logs);
        $this->assertSame(1, $logs->where('status', 'sent')->count());
        $this->assertStringContainsString('Pengumuman Rapat RT', $logs->firstWhere('status', 'sent')->message ?? '');
    }

    public function test_publication_broadcast_deduplicates_household_phone(): void
    {
        $this->fakeWahaWorking();

        $profile = RtProfile::create([
            'rt_number' => '018',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 018',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 018',
            'email' => 'pub-dedup@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010301',
            'address' => 'Jl. Dedup',
            'status' => 'aktif',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010301',
            'name' => 'Kepala Dedup',
            'phone' => '081234567830',
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010302',
            'name' => 'Anggota Dedup',
            'phone' => null,
            'whatsapp_notify' => true,
            'is_head_of_family' => false,
            'relationship_to_head' => 'Anak',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $publication = RtPublication::create([
            'rt_profile_id' => $profile->id,
            'type' => RtPublicationType::Pengumuman,
            'judul' => 'Pengumuman Dedup',
            'ringkasan' => 'Satu pesan per nomor keluarga.',
            'tanggal' => now()->toDateString(),
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($staff)
            ->post(route('rt.pengumuman.whatsapp', $publication), [
                'recipient_mode' => 'all',
            ])
            ->assertRedirect(route('rt.pengumuman.edit', $publication))
            ->assertSessionHas('success');

        $logs = NotificationLog::query()
            ->forPublication($publication->id)
            ->get();

        $this->assertCount(1, $logs);
        $this->assertSame('sent', $logs->first()->status);
        $this->assertSame('081234567830', $logs->first()->phone);
    }

    public function test_publication_edit_page_shows_whatsapp_section(): void
    {
        $this->fakeWahaWorking();

        [, $staff, $publication] = $this->seedPublication();

        NotificationLog::create([
            'rt_publication_id' => $publication->id,
            'resident_id' => Resident::first()->id,
            'phone' => '081234567820',
            'event' => 'publication_broadcast',
            'message' => 'Test broadcast',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.pengumuman.edit', $publication))
            ->assertOk()
            ->assertSee('Kirim WhatsApp ke warga', false)
            ->assertSee('Semua warga RT dengan notifikasi WhatsApp aktif', false)
            ->assertSee('Pilih warga tertentu', false)
            ->assertSee('Pengumuman dikirim', false);
    }

    public function test_publication_broadcast_can_target_selected_residents_only(): void
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

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'pub-select@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $householdA = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010401',
            'address' => 'Jl. Select A',
            'status' => 'aktif',
        ]);

        $residentA = Resident::create([
            'household_id' => $householdA->id,
            'nik' => '3201010101010401',
            'name' => 'Warga Terpilih',
            'phone' => '081234567841',
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $householdB = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010402',
            'address' => 'Jl. Select B',
            'status' => 'aktif',
        ]);

        Resident::create([
            'household_id' => $householdB->id,
            'nik' => '3201010101010402',
            'name' => 'Warga Lain',
            'phone' => '081234567842',
            'whatsapp_notify' => true,
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $publication = RtPublication::create([
            'rt_profile_id' => $profile->id,
            'type' => RtPublicationType::Kegiatan,
            'judul' => 'Kegiatan Kerja Bakti',
            'ringkasan' => 'Kerja bakti lingkungan.',
            'tanggal' => now()->toDateString(),
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($staff)
            ->post(route('rt.kegiatan.whatsapp', $publication), [
                'recipient_mode' => 'selected',
                'resident_ids' => [$residentA->id],
            ])
            ->assertRedirect(route('rt.kegiatan.edit', $publication))
            ->assertSessionHas('success');

        $logs = NotificationLog::query()
            ->forPublication($publication->id)
            ->get();

        $this->assertCount(1, $logs);
        $this->assertSame('sent', $logs->first()->status);
        $this->assertSame('081234567841', $logs->first()->phone);
        $this->assertSame($residentA->id, $logs->first()->resident_id);
    }
}
