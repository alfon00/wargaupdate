<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RtProfileStaffAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_create_form_lists_rt_one_through_sixteen(): void
    {
        $admin = User::create([
            'name' => 'Admin Kelurahan',
            'email' => 'admin@layananwarga.my.id',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.create'));

        $response->assertOk();

        for ($i = 1; $i <= 16; $i++) {
            $response->assertSee('RT '.str_pad((string) $i, 3, '0', STR_PAD_LEFT), false);
        }

        $this->assertSame(16, RtProfile::forStaffAssignment()->count());
    }

    public function test_for_staff_assignment_creates_missing_inauga_profiles(): void
    {
        $this->assertSame(0, RtProfile::inauga()->count());

        $profiles = RtProfile::forStaffAssignment();

        $this->assertSame(16, $profiles->count());
        $this->assertSame(
            ['001', '002', '003', '004', '005', '006', '007', '008', '009', '010', '011', '012', '013', '014', '015', '016'],
            $profiles->pluck('rt_number')->all()
        );
        $this->assertTrue($profiles->every(fn (RtProfile $profile) => str_contains((string) $profile->kelurahan, 'Inauga')));
    }
}
