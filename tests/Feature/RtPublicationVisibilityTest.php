<?php

namespace Tests\Feature;

use App\Enums\RtPublicationType;
use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\RtPublication;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RtPublicationVisibilityTest extends TestCase
{
    private function createRtWithStaff(): array
    {
        $profile = RtProfile::create([
            'slug' => 'rt-vis',
            'rt_number' => '099',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 099',
        ]);

        $user = User::create([
            'name' => 'Ketua RT 099',
            'email' => 'ketua-vis@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        return [$profile, $user];
    }

    private function createPengumuman(RtProfile $profile, array $overrides = []): RtPublication
    {
        return RtPublication::create(array_merge([
            'rt_profile_id' => $profile->id,
            'type' => RtPublicationType::Pengumuman,
            'judul' => 'Pengumuman uji',
            'ringkasan' => 'Ringkasan pengumuman uji.',
            'is_published' => true,
            'published_at' => now(),
        ], $overrides));
    }

    public function test_pengumuman_with_future_expires_at_is_visible_on_public_page(): void
    {
        [$profile] = $this->createRtWithStaff();

        $this->createPengumuman($profile, [
            'judul' => 'Pengumuman aktif besok',
            'expires_at' => now('Asia/Jayapura')->addDay()->toDateString(),
        ]);

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Pengumuman aktif besok', false);
    }

    public function test_pengumuman_with_past_expires_at_is_hidden_on_public_page(): void
    {
        [$profile] = $this->createRtWithStaff();

        $this->createPengumuman($profile, [
            'judul' => 'Pengumuman kedaluwarsa kemarin',
            'expires_at' => now('Asia/Jayapura')->subDay()->toDateString(),
        ]);

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertDontSee('Pengumuman kedaluwarsa kemarin', false);
    }

    public function test_pengumuman_without_expires_at_within_30_days_is_visible(): void
    {
        [$profile] = $this->createRtWithStaff();

        $this->createPengumuman($profile, [
            'judul' => 'Pengumuman 10 hari lalu',
            'published_at' => now('Asia/Jayapura')->subDays(10),
            'expires_at' => null,
        ]);

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Pengumuman 10 hari lalu', false);
    }

    public function test_pengumuman_without_expires_at_older_than_30_days_is_hidden(): void
    {
        [$profile] = $this->createRtWithStaff();

        $this->createPengumuman($profile, [
            'judul' => 'Pengumuman 31 hari lalu',
            'published_at' => now('Asia/Jayapura')->subDays(31),
            'expires_at' => null,
        ]);

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertDontSee('Pengumuman 31 hari lalu', false);
    }

    public function test_expired_pengumuman_still_visible_in_rt_panel(): void
    {
        [$profile, $user] = $this->createRtWithStaff();

        $this->createPengumuman($profile, [
            'judul' => 'Pengumuman panel kedaluwarsa',
            'expires_at' => now('Asia/Jayapura')->subDay()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('rt.pengumuman.index'))
            ->assertOk()
            ->assertSee('Pengumuman panel kedaluwarsa', false)
            ->assertSee('Kedaluwarsa', false);
    }

    public function test_rt_can_store_pengumuman_with_expires_at(): void
    {
        [$profile, $user] = $this->createRtWithStaff();
        $expiresAt = now('Asia/Jayapura')->addDays(14)->toDateString();

        $this->actingAs($user)
            ->post(route('rt.pengumuman.store'), [
                'judul' => 'Rapat warga bulanan',
                'ringkasan' => 'Undangan rapat warga RT.',
                'tanggal' => now('Asia/Jayapura')->addDays(3)->toDateString(),
                'expires_at' => $expiresAt,
            ])
            ->assertRedirect(route('rt.pengumuman.index'));

        $publication = RtPublication::query()
            ->where('rt_profile_id', $profile->id)
            ->where('judul', 'Rapat warga bulanan')
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame(RtPublicationType::Pengumuman, $publication->type);
        $this->assertSame($expiresAt, $publication->expires_at?->toDateString());
    }

    public function test_public_pengumuman_shows_berlaku_hingga_label(): void
    {
        [$profile] = $this->createRtWithStaff();
        $expiresAt = now('Asia/Jayapura')->addDays(7);

        $this->createPengumuman($profile, [
            'judul' => 'Pengumuman berlabel',
            'expires_at' => $expiresAt->toDateString(),
        ]);

        $label = $expiresAt->locale('id')->translatedFormat('d M Y');

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Berlaku hingga '.$label, false);
    }

    public function test_pengumuman_edit_page_has_delete_action_not_index(): void
    {
        [$profile, $user] = $this->createRtWithStaff();

        $publication = $this->createPengumuman($profile, [
            'judul' => 'Pengumuman hapus di edit',
        ]);

        $this->actingAs($user)
            ->get(route('rt.pengumuman.edit', $publication))
            ->assertOk()
            ->assertSee(route('rt.pengumuman.destroy', $publication, false), false)
            ->assertSee('Hapus Pengumuman', false);

        $this->actingAs($user)
            ->get(route('rt.pengumuman.index'))
            ->assertOk()
            ->assertDontSee('Hapus Pengumuman', false);
    }
}
