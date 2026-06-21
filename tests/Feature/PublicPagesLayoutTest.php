<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\RtPublicationType;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\RtPublication;
use App\Models\ServiceType;
use App\Models\SuratIdentityVerification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPagesLayoutTest extends TestCase
{
    public function test_services_index_does_not_have_quick_actions(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertDontSee('lw-home-quick-actions', false)
            ->assertDontSee('Mulai dari sini', false)
            ->assertSee('Layanan Warga RT', false)
            ->assertDontSee('Inauga', false)
            ->assertDontSee('Kelurahan', false)
            ->assertDontSee('Kabupaten Mimika', false)
            ->assertSee('lw-service-hub-grid', false);
    }

    public function test_contact_page_uses_profile_style_hero(): void
    {
        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('lw-contact-page lw-contact-split', false)
            ->assertSee('lw-contact-hero', false)
            ->assertSee('lw-profile-hero__title', false)
            ->assertSee('Pengaduan', false)
            ->assertSee('Komunikasi warga', false)
            ->assertDontSee('class="lw-hero-title-accent"', false)
            ->assertSee('lw-track-hero-grid', false)
            ->assertSee('lw-track-intro', false)
            ->assertSee('lw-contact-forms', false)
            ->assertSee('lw-contact-form-card', false)
            ->assertSee('lw-contact-board', false)
            ->assertSee('Kirim laporan ke pengurus RT', false)
            ->assertDontSee('Cara Mengirim Laporan', false)
            ->assertDontSee('Pertanyaan Seputar Pengaduan', false)
            ->assertDontSee('class="lw-track-bottom-grid"', false)
            ->assertSee('lw-track-split__form', false)
            ->assertDontSee('class="lw-section-tag"', false)
            ->assertDontSee('Formulir', false)
            ->assertDontSee('Form pengaduan', false)
            ->assertDontSee('>Ringkasan<', false)
            ->assertDontSee('Nomor permohonan', false)
            ->assertDontSee('name="subject"', false)
            ->assertDontSee('class="lw-form-grid--labeled"', false)
            ->assertDontSee('lw-form-card lw-mt-4', false)
            ->assertDontSee('<div class="lw-site-frame">', false);
    }

    public function test_services_page_uses_profile_style_hero(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('lw-services-hero', false)
            ->assertSee('lw-profile-hero__title', false)
            ->assertSee('Layanan Warga RT', false)
            ->assertDontSee('class="lw-hero-title-accent"', false)
            ->assertDontSee('Menu layanan', false)
            ->assertDontSee('Katalog &amp; permohonan', false)
            ->assertDontSee('class="lw-services-admin-intro"', false)
            ->assertSee('lw-services-board', false)
            ->assertSee('lw-service-hub-grid', false)
            ->assertDontSee('<div class="lw-site-frame">', false);
    }

    public function test_services_index_shows_surat_flow_steps(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('lw-service-flow-tabs', false)
            ->assertSee('Alur layanan', false)
            ->assertSee('Pilih jenis surat', false)
            ->assertSee('Baca persyaratan', false)
            ->assertSee('Verifikasi identitas', false)
            ->assertSee('notifikasi WhatsApp', false)
            ->assertSee('Ambil salinan fisik di sekretariat RT', false)
            ->assertSee('Lacak status via menu Lacak', false)
            ->assertSee('Pendataan ulang', false)
            ->assertSee('Unggah berkas &amp; verifikasi wajah', false)
            ->assertSee('Keluarga tercatat', false)
            ->assertDontSee('id="persyaratan-heading"', false)
            ->assertDontSee('Persyaratan umum', false)
            ->assertSeeInOrder([
                'layanan-hub-heading',
                'catalog-heading-alur',
            ], false)
            ->assertSee('Pilih jenis surat →', false)
            ->assertSee('id="alur-surat"', false)
            ->assertSee('id="alur-pendataan-ulang"', false)
            ->assertSee('id="alur-pendataan-warga"', false);
    }

    public function test_service_show_page_has_apply_without_pendataan_button(): void
    {
        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Keterangan Domisili',
            'is_active' => true,
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Ajukan surat pengantar', false)
            ->assertDontSee('lw-btn-secondary">Pendataan ulang', false);
    }

    public function test_apply_page_does_not_repeat_requirements(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua-apply-req@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010001',
            'name' => 'Warga Uji',
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
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Keterangan Domisili',
            'required_fields' => ['KK', 'KTP'],
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->get(route('services.apply', $service))
            ->assertOk()
            ->assertSee('Batalkan permohonan', false)
            ->assertSee('Data pemohon', false)
            ->assertSee('Lampiran berkas', false)
            ->assertSee('Pengurus RT memakai lampiran', false)
            ->assertSee('halaman depan KK', false)
            ->assertSee('Kartu Keluarga (KK)', false)
            ->assertSee('KTP atau KIA', false)
            ->assertSee('name="documents[0]"', false)
            ->assertDontSee('Jumlah orang', false)
            ->assertDontSee('Orang yang diajukan surat', false)
            ->assertDontSee('data-surat-apply-subjects', false)
            ->assertSee('Terima notifikasi status permohonan via WhatsApp', false)
            ->assertSee('type="hidden" name="whatsapp_notify"', false)
            ->assertSee('checked disabled', false)
            ->assertDontSee('name="documents[]"', false)
            ->assertDontSee('Persyaratan umum', false)
            ->assertDontSee('Unggah berkas persyaratan melalui formulir', false)
            ->assertDontSee('id="birth_place"', false);
    }

    public function test_apply_page_logout_clears_surat_session(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua-apply-logout@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010001',
            'name' => 'Warga Uji',
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
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Keterangan Domisili',
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
            'surat_intended_service_code' => $service->code,
        ])
            ->from(route('services.apply', $service))
            ->post(route('services.surat.logout'))
            ->assertRedirect(route('services.surat'))
            ->assertSessionHas('info')
            ->assertSessionMissing('surat_resident_id')
            ->assertSessionMissing('surat_verification_id')
            ->assertSessionMissing('surat_intended_service_code');
    }

    public function test_disclaimer_appears_in_footer_not_main_banner(): void
    {
        $disclaimerSnippet = 'Portal RT — bukan situs Dukcapil';

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('lw-footer-disclaimer', false)
            ->assertSee($disclaimerSnippet, false)
            ->assertDontSee('Inauga', false)
            ->assertDontSee('Kelurahan', false)
            ->assertDontSee('Kabupaten Mimika', false);

        $this->get(route('services.pendataan-ulang'))
            ->assertOk()
            ->assertSee('lw-footer-disclaimer', false)
            ->assertSee($disclaimerSnippet, false)
            ->assertDontSee('rounded-lg border border-emerald-200 lw-surface', false);
    }

    public function test_home_footer_shows_trust_and_contact_info(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('lw-footer-disclaimer', false)
            ->assertSee('Keamanan & keaslian situs', false)
            ->assertSee('Layanan Warga RT', false)
            ->assertDontSee('lw-footer-contact', false);

        $html = $response->getContent();
        preg_match('/<footer class="lw-footer"[^>]*>.*?<\/footer>/s', $html, $footer);
        $this->assertStringNotContainsString('Inauga', $footer[0] ?? '');
        $this->assertStringNotContainsString('Kelurahan', $footer[0] ?? '');
        $this->assertStringNotContainsString('Kabupaten Mimika', $footer[0] ?? '');
    }

    public function test_profile_page_loads(): void
    {
        $this->get(route('profile.index'))
            ->assertOk()
            ->assertSee('Profil RT', false)
            ->assertSee('lw-profile-rt-grid', false)
            ->assertSee('lw-profile-lurah-card', false)
            ->assertSee(config('kelurahan.lurah.nama'), false)
            ->assertSee('lw-profile-wilayah', false)
            ->assertSee('Profil &amp; RT', false)
            ->assertDontSee('Kabupaten Mimika', false)
            ->assertSee('Visi', false)
            ->assertSee('Misi', false)
            ->assertDontSee('id="rt-picker"', false)
            ->assertDontSee('lw-profile-picker-btn', false)
            ->assertDontSee('<div class="lw-site-frame">', false);
    }

    public function test_profile_show_page_is_simplified(): void
    {
        $profile = RtProfile::create([
            'slug' => 'rt-001-rw-001',
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
            'ketua_rw' => 'Ketua RW 001',
            'visi' => 'Visi placeholder yang tidak boleh ditampilkan.',
            'misi' => '1. Misi placeholder.',
            'jam_layanan' => 'Senin–Jumat 08.00–14.00 WIT',
            'alamat_kantor' => 'Kantor RT 001 RW 001, Kelurahan Inauga',
        ]);

        User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua-001@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $this->get(route('profile.show', $profile))
            ->assertOk()
            ->assertSee('lw-profile-rt-show-card', false)
            ->assertSee('Kembali ke daftar RT', false)
            ->assertSee('Ketua RT 001', false)
            ->assertSee('Senin–Jumat 08.00–14.00 WIT', false)
            ->assertDontSee('<dt>Visi</dt>', false)
            ->assertDontSee('<dt>Misi</dt>', false)
            ->assertDontSee('Visi placeholder yang tidak boleh ditampilkan.', false)
            ->assertDontSee('Misi placeholder.', false);
    }

    public function test_activities_page_uses_new_layout(): void
    {
        $profile = RtProfile::create([
            'slug' => 'rt-001',
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua-kegiatan@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        RtPublication::create([
            'rt_profile_id' => $profile->id,
            'type' => RtPublicationType::Kegiatan,
            'judul' => 'Kerja bakti lingkungan',
            'ringkasan' => 'Gotong royong membersihkan saluran RT.',
            'tanggal' => now('Asia/Jayapura')->addDays(3)->toDateString(),
            'lokasi' => 'Wilayah RT 001',
            'foto_path' => 'images/kegiatan/placeholder.svg',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('lw-activities-page', false)
            ->assertSee('lw-activities-hero', false)
            ->assertSee('lw-profile-hero__title', false)
            ->assertSee('Agenda warga', false)
            ->assertDontSee('class="lw-activities-hero__calendar-art"', false)
            ->assertSee('lw-activities-announce-panel', false)
            ->assertSee('lw-activities-event-card__photo', false)
            ->assertSee('Foto dokumentasi: Kerja bakti lingkungan', false)
            ->assertSee('Kerja bakti lingkungan', false)
            ->assertSee('Cari kegiatan...', false)
            ->assertSee('Semua', false)
            ->assertSee('Hari Ini', false)
            ->assertSee('Minggu Ini', false)
            ->assertSee('Akan Datang', false)
            ->assertSee('Selesai', false)
            ->assertSee('Pengumuman', false)
            ->assertDontSee('id="lw-activities-calendar"', false)
            ->assertDontSee('id="lw-gallery-grid"', false)
            ->assertDontSee('class="lw-page-subnav"', false)
            ->assertDontSee('<div class="lw-site-frame">', false);
    }

    public function test_public_layout_does_not_use_site_frame_wrapper(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('<div class="lw-site-frame">', false)
            ->assertSee('Akses Pengurus', false)
            ->assertSee('lw-home-hero-v3-shell', false);
    }

    public function test_surat_catalog_uses_public_empty_state(): void
    {
        $this->get(route('services.surat'))
            ->assertOk()
            ->assertSee('class="lw-empty-state', false)
            ->assertDontSee('class="lw-panel-empty"', false);
    }

    public function test_contact_form_uses_track_split_layout(): void
    {
        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('lw-track-split__form', false)
            ->assertSee('lw-track-split__submit', false)
            ->assertDontSee('class="lw-form-grid--labeled"', false)
            ->assertDontSee('class="lw-form--labeled"', false);
    }

    public function test_inner_pages_use_compact_body_class_and_hero(): void
    {
        $innerRoutes = [
            'profile.index' => 'Profil &amp; RT',
            'activities.index' => 'Kegiatan &amp; Pengumuman',
            'services.index' => 'Layanan Warga RT',
            'contact.create' => 'Pengaduan',
            'login.hub' => 'Akses Pengurus RT',
        ];

        foreach ($innerRoutes as $routeName => $title) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('class="lw-shell lw-page-inner"', false)
                ->assertSee('lw-profile-hero__title', false)
                ->assertSee($title, false);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="lw-shell lw-page-home"', false)
            ->assertDontSee('class="lw-shell lw-page-inner"', false);
    }

    public function test_home_page_uses_page_home_body_class(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="lw-shell lw-page-home"', false)
            ->assertDontSee('class="lw-shell lw-page-inner"', false)
            ->assertSee('lw-home-hero-v3-shell', false)
            ->assertSee('lw-home-page', false)
            ->assertSee('Layanan Warga RT', false)
            ->assertSee('Portal warga', false)
            ->assertDontSee('SISTEM LAYANAN WARGA RT', false)
            ->assertSee('Sudah mengajukan?', false)
            ->assertSee('Surat pengantar RT', false)
            ->assertSee('Pendataan warga', false)
            ->assertDontSee('Pendataan Ulang Warga', false)
            ->assertDontSee('>Layanan Administrasi</h3>', false)
            ->assertSee('lw-home-wa-strip', false)
            ->assertSee('id="home-faq-heading"', false)
            ->assertSee('Panduan Penggunaan Layanan', false)
            ->assertSee('Bagaimana cara mengambil surat yang sudah jadi?', false)
            ->assertSee('pendataan ulang, atau pendataan warga', false);
    }

    public function test_public_navbar_uses_descriptive_menu_labels(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="lw-nav"', false)
            ->assertSee('lw-nav-link-active', false)
            ->assertSee('Kegiatan &amp; Pengumuman', false)
            ->assertSee('Pengaduan', false)
            ->assertSee('Lacak Permohonan', false);

        $this->get(route('track.form'))
            ->assertOk()
            ->assertSee('class="lw-nav"', false)
            ->assertSee('lw-nav-link-active', false)
            ->assertSee('Lacak Permohonan', false);
    }

    public function test_profile_page_uses_compact_inner_layout(): void
    {
        $this->get(route('profile.index'))
            ->assertOk()
            ->assertSee('class="lw-shell lw-page-inner"', false)
            ->assertSee('lw-profile-board', false)
            ->assertSee('lw-profile-lurah-card', false)
            ->assertSee('lw-profile-rt-grid', false)
            ->assertSee('lw-profile-wilayah', false);
    }

    public function test_track_page_renders_compact_form_classes(): void
    {
        $this->get(route('track.form'))
            ->assertOk()
            ->assertSee('class="lw-shell lw-page-inner"', false)
            ->assertSee('lw-profile-hero__title', false)
            ->assertSee('Lacak Permohonan', false)
            ->assertSee('lw-track-form-card', false)
            ->assertSee('lw-form-label', false)
            ->assertSee('Nomor permohonan', false);
    }

    public function test_public_nav_pages_do_not_use_site_frame_wrapper(): void
    {
        $routes = [
            'activities.index',
            'contact.create',
            'track.form',
            'services.index',
            'login.hub',
        ];

        foreach ($routes as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertDontSee('<div class="lw-site-frame">', false);
        }
    }

    public function test_security_page_uses_profile_layout_without_redundant_footer(): void
    {
        $this->get(route('security'))
            ->assertOk()
            ->assertSee('lw-security-page', false)
            ->assertSee('lw-profile-hero__title', false)
            ->assertSee('Keamanan &amp; keaslian situs', false)
            ->assertSee('lw-security-panel', false)
            ->assertSee('/.well-known/security.txt', false)
            ->assertDontSee('class="lw-footer-top"', false)
            ->assertDontSee('class="lw-footer-disclaimer"', false)
            ->assertDontSee('Pelajari lebih lanjut →', false)
            ->assertDontSee('class="lw-form-card"', false);
    }

    public function test_pembaruan_redirects_to_pendataan_ulang(): void
    {
        $this->get(route('services.pembaruan'))
            ->assertRedirect('/layanan/pendataan-ulang');
    }

    public function test_pendataan_ulang_page_uses_form_card(): void
    {
        $this->get(route('services.pendataan-ulang'))
            ->assertOk()
            ->assertDontSee('lw-form-step-nav', false)
            ->assertSee('class="lw-form-card"', false);
    }

    public function test_pendataan_warga_page_uses_form_card(): void
    {
        $this->get(route('services.pendataan-warga'))
            ->assertOk()
            ->assertSee('Pendataan warga', false)
            ->assertSee('lw-form-card', false)
            ->assertSee('data-pendataan-warga-page', false)
            ->assertDontSee('name="document_ktp"', false)
            ->assertDontSee('Scan/foto KTP kepala KK', false);
    }

    public function test_pendataan_warga_success_page_loads_after_submit(): void
    {
        Storage::fake('local');

        $rt = RtProfile::create([
            'rt_number' => '099',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 099',
        ]);

        $this->post(route('services.pendataan-warga.store'), [
            'rt_profile_id' => $rt->id,
            'family_card_number' => '3201010101010999',
            'address' => 'Jl. Uji Success',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
            'phone' => '081299900099',
            'whatsapp_notify' => '1',
            'head_face_descriptor' => $this->sampleFaceDescriptor(),
            'head_selfie_data' => $this->sampleSelfieDataUri(),
            'members' => [[
                'name' => 'Warga Success',
                'nik' => '3201010101010999',
                'relationship' => 'Kepala Keluarga',
                'birth_place' => 'Timika',
                'birth_date' => '1990-01-01',
                'gender' => 'Laki-laki',
                'occupation' => 'Pegawai',
                'education' => 'SMA/SMK',
                'religion' => 'Islam',
                'marital_status' => 'Kawin',
                'citizenship' => 'WNI',
                'document_id' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ]],
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('services.pendataan-warga.success'));

        $this->get(route('services.pendataan-warga.success'))
            ->assertOk()
            ->assertSee('Pengajuan pendataan warga diterima', false)
            ->assertSee('Warga Success', false);
    }

    public function test_apply_form_uses_labeled_layout(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua-apply-layout@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010001',
            'name' => 'Warga Apply',
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
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->get(route('services.apply', $service))
            ->assertOk()
            ->assertSee('lw-form--labeled', false)
            ->assertSee('lw-form-grid--labeled', false);
    }

    public function test_public_navbar_does_not_show_today_date(): void
    {
        $this->travelTo(Carbon::parse('2026-06-11 10:00:00', 'Asia/Jayapura'));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('lw-nav-date', false)
            ->assertDontSee('Hari ini', false)
            ->assertDontSee('Kamis, 11 Juni 2026', false);
    }

    /** @return array<int, float> */
    private function sampleFaceDescriptor(): array
    {
        return array_map(static fn (int $i) => round($i / 128, 6), range(0, 127));
    }

    private function sampleSelfieDataUri(): string
    {
        return 'data:image/jpeg;base64,'.base64_encode(str_repeat('0', 1200));
    }
}
