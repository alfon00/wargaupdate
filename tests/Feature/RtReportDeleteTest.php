<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\CitizenReport;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtReportDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: RtProfile} */
    private function seedRtStaff(string $rtNumber = '008'): array
    {
        $profile = RtProfile::create([
            'rt_number' => $rtNumber,
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-report-delete@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        return [$staff, $profile];
    }

    private function seedReport(RtProfile $profile, string $number = 'LPR008-2026060001'): CitizenReport
    {
        return CitizenReport::create([
            'report_number' => $number,
            'rt_profile_id' => $profile->id,
            'category' => 'pengaduan_lingkungan',
            'reporter_name' => 'Pelapor Uji',
            'phone' => '081200000099',
            'subject' => 'Sampah menumpuk',
            'message' => 'Perlu segera dibersihkan.',
            'status' => ReportStatus::Baru,
        ]);
    }

    public function test_rt_owner_can_delete_report(): void
    {
        [$staff, $profile] = $this->seedRtStaff();
        $report = $this->seedReport($profile);

        $this->actingAs($staff)
            ->delete(route('rt.reports.destroy', $report))
            ->assertRedirect(route('rt.reports.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('citizen_reports', ['id' => $report->id]);
    }

    public function test_other_rt_cannot_delete_report(): void
    {
        [, $profile] = $this->seedRtStaff();
        $report = $this->seedReport($profile);

        $otherProfile = RtProfile::create([
            'rt_number' => '009',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 9',
        ]);

        $otherStaff = User::create([
            'name' => 'Ketua RT 9',
            'email' => 'ketua-rt9-report@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($otherStaff)
            ->delete(route('rt.reports.destroy', $report))
            ->assertNotFound();

        $this->assertDatabaseHas('citizen_reports', ['id' => $report->id]);
    }

    public function test_delete_report_removes_photo_file(): void
    {
        Storage::fake('local');
        [$staff, $profile] = $this->seedRtStaff();
        $report = $this->seedReport($profile, 'LPR008-2026060002');

        $photoPath = 'reports/'.$report->id.'/foto.jpg';
        Storage::disk('local')->put($photoPath, 'image-content');
        $report->update(['photo_path' => $photoPath]);

        $this->actingAs($staff)
            ->delete(route('rt.reports.destroy', $report))
            ->assertRedirect(route('rt.reports.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('citizen_reports', ['id' => $report->id]);
        Storage::disk('local')->assertMissing($photoPath);
    }

    public function test_show_page_has_delete_action(): void
    {
        [$staff, $profile] = $this->seedRtStaff();
        $report = $this->seedReport($profile, 'LPR008-2026060003');

        $this->actingAs($staff)
            ->get(route('rt.reports.show', $report))
            ->assertOk()
            ->assertSee(route('rt.reports.destroy', $report, false), false)
            ->assertSee('Hapus laporan', false);

        $this->actingAs($staff)
            ->get(route('rt.reports.index'))
            ->assertOk()
            ->assertDontSee('Hapus laporan', false);
    }
}
