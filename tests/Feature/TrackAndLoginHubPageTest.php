<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrackAndLoginHubPageTest extends TestCase
{
    public function test_track_form_uses_track_split_layout(): void
    {
        $response = $this->get(route('track.form'));

        $response->assertOk();
        $response->assertSee('lw-track-page lw-track-split', false);
        $response->assertSee('lw-track-hero', false);
        $response->assertSee('id="track-hero-heading"', false);
        $response->assertSee('lw-track-board--centered', false);
        $response->assertSee('Pelacakan permohonan', false);
        $response->assertSee('lw-profile-hero__title', false);
        $response->assertSee('lw-container--wide', false);
        $response->assertSee('lw-track-hero-grid', false);
        $response->assertSee('lw-track-hero-grid--solo', false);
        $response->assertDontSee('class="lw-track-intro"', false);
        $response->assertSee('lw-track-forms', false);
        $response->assertSee('lw-track-bottom-grid', false);
        $response->assertSee('lw-track-bottom-grid--solo', false);
        $response->assertSee('lw-track-info-card', false);
        $response->assertSee('lw-track-form-card', false);
        $response->assertSee('lw-form-card', false);
        $response->assertDontSee('lw-track-split__meta', false);
        $response->assertDontSee('lw-track-aside', false);
        $response->assertDontSee('lw-track-page--modern', false);
        $response->assertDontSee('lw-track-split__illust-inner', false);
        $response->assertDontSee('lw-track-split__card', false);
        $response->assertDontSee('class="lw-form-panel"', false);
        $response->assertDontSee('class="lw-track-card"', false);
        $response->assertDontSee('id="track-form-title"', false);
        $response->assertSee('Lacak Permohonan', false);
        $response->assertDontSee('Cari permohonan', false);
        $response->assertDontSee('class="lw-track-split__badge"', false);
        $response->assertDontSee('>LACAK<', false);
        $response->assertDontSee('Cek status permohonan Anda', false);
        $response->assertDontSee('class="lw-track-benefit"', false);
        $response->assertDontSee('Aman &amp; terpercaya', false);
        $response->assertDontSee('Proses transparan', false);
        $response->assertDontSee('Mudah dari HP atau komputer', false);
        $response->assertDontSee('class="lw-track-divider"', false);
        $response->assertDontSee('atau cari dengan', false);
        $response->assertDontSee('class="lw-track-alt"', false);
        $response->assertDontSee('lw-track-form-section', false);
        $response->assertDontSee('lw-track-intro__points', false);
        $response->assertDontSee('Cara Melacak Permohonan', false);
        $response->assertDontSee('class="lw-flow-step"', false);
        $response->assertDontSee('class="lw-track-flow-grid"', false);
        $response->assertDontSee('class="lw-track-steps-note"', false);
        $response->assertDontSee('Siapkan nomor', false);
        $response->assertDontSee('Buka halaman Lacak', false);
        $response->assertSee('Kirim laporan', false);
        $response->assertDontSee('class="lw-track-steps"', false);
        $response->assertDontSee('lw-track-split__steps', false);
        $response->assertDontSee('lw-home-faq-icon', false);
        $response->assertSee('notifikasi WhatsApp', false);
        $response->assertDontSee('NIK (opsional)', false);
        $response->assertDontSee('name="nik"', false);
        $response->assertDontSee('Mengapa NIK tidak cocok?', false);
        $response->assertSee('Bagaimana cara mengetahui ada pembaruan status?', false);
        $response->assertSee('Surat sudah siap, apa yang harus dilakukan?', false);
        $response->assertDontSee('Cari dengan NIK saja', false);
        $response->assertDontSee('Cari dengan nomor WhatsApp', false);
        $response->assertSee('name="mode" value="number"', false);
        $response->assertDontSee('name="mode" value="nik"', false);
        $response->assertDontSee('name="mode" value="whatsapp"', false);
        $response->assertSee('>Cari</button>', false);
        $response->assertSee('Pertanyaan Seputar Pelacakan', false);
        $response->assertSee('id="track-faq-heading"', false);
        $response->assertDontSee('>Panduan<', false);
        $response->assertDontSee('class="lw-section-tag"', false);
        $response->assertSee('lw-faq-section--track', false);
    }

    public function test_login_hub_uses_auth_split_layout(): void
    {
        $response = $this->get(route('login.hub'));

        $response->assertOk();
        $response->assertSee('lw-auth-split', false);
        $response->assertSee('lw-auth-page', false);
        $response->assertDontSee('class="lw-form-panel"', false);
        $response->assertDontSee('class="lw-auth-card"', false);
        $response->assertDontSee('class="lw-auth-split__illust"', false);
        $response->assertDontSee('class="lw-auth-split__svg"', false);
        $response->assertSee('lw-auth-hero', false);
        $response->assertSee('lw-profile-hero__title', false);
        $response->assertSee('Akses Pengurus RT', false);
        $response->assertSee('Masuk ke panel', false);
        $response->assertDontSee('← Beranda', false);
        $response->assertSee('lw-track-hero-grid', false);
        $response->assertSee('lw-track-benefits', false);
        $response->assertSee('Sesi terenkripsi', false);
        $response->assertSee('Email pengurus', false);
        $response->assertSee('verifikasi pendataan', false);
        $response->assertDontSee('Keamanan akses pengurus', false);
        $response->assertDontSee('Akses Khusus Pengurus RT', false);
        $response->assertDontSee('Username', false);
        $response->assertSee('lw-auth-split__submit', false);
        $response->assertSee('>Masuk</button>', false);
        $response->assertDontSee('>Login</button>', false);
        $response->assertDontSee('Masuk Pengurus', false);
        $response->assertDontSee('Lupa password', false);
    }

    public function test_track_list_uses_result_card_layout(): void
    {
        $profile = \App\Models\RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        $household = \App\Models\Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        $resident = \App\Models\Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010001',
            'name' => 'Warga Lacak',
            'phone' => '081234567890',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'occupation' => 'Karyawan',
            'education' => 'SMA',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'domicile_status' => \App\Enums\DomicileStatus::Aktif,
        ]);

        $service = \App\Models\ServiceType::create([
            'code' => 'surat_usaha',
            'name' => 'Surat Keterangan Usaha',
            'description' => 'Surat pengantar usaha.',
            'is_active' => true,
        ]);

        \App\Models\Application::create([
            'application_number' => 'RT001-2026060001',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => \App\Enums\ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan uji',
            'submitted_at' => now(),
        ]);

        $this->post(route('track.show'), [
            'mode' => 'nik',
            'nik' => '3201010101010001',
        ])
            ->assertOk()
            ->assertSee('lw-track-result-list', false)
            ->assertSee('lw-track-result-card', false)
            ->assertSee('RT001-2026060001', false)
            ->assertSee('Permohonan ditemukan', false);
    }

    public function test_track_show_displays_manual_letter_number_when_ready(): void
    {
        $profile = \App\Models\RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $household = \App\Models\Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010088',
            'address' => 'Jl. Track Test',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = \App\Models\Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010088',
            'name' => 'Warga Track',
            'phone' => '081234567888',
            'is_head_of_family' => true,
            'domicile_status' => \App\Enums\DomicileStatus::Aktif,
        ]);

        $service = \App\Models\ServiceType::create([
            'code' => 'surat_track_manual',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $letterNumber = 'RT008/SK/06/2026/088';

        \App\Models\Application::create([
            'application_number' => 'RT008-2026060088',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => \App\Enums\ApplicationStatus::SiapDiambil,
            'purpose' => 'Keperluan uji',
            'submitted_at' => now(),
            'completed_at' => now(),
            'form_data' => [
                'manual_letter' => [
                    'number' => $letterNumber,
                    'issued_at' => now()->toIso8601String(),
                    'issued_by' => 1,
                ],
            ],
        ]);

        $this->post(route('track.show'), [
            'application_number' => 'RT008-2026060088',
        ])
            ->assertOk()
            ->assertSee($letterNumber, false)
            ->assertSee('Ambil surat fisik di sekretariat', false);
    }
}
