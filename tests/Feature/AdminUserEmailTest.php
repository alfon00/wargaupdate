<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\StaffEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserEmailTest extends TestCase
{
    use RefreshDatabase;

    private function createKelurahanUser(): User
    {
        return User::create([
            'name' => 'Admin Kelurahan',
            'email' => 'admin@layananwarga.my.id',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);
    }

    public function test_create_form_shows_fixed_email_suffix(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('lw-staff-email-input', false)
            ->assertSee(StaffEmail::suffix(), false)
            ->assertSee('name="email_local"', false)
            ->assertDontSee('name="email"', false);
    }

    public function test_store_composes_staff_email_from_local_part(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Ketua RT 008',
                'email_local' => 'ketua.rt008',
                'password' => 'password123',
                'role' => UserRole::Kelurahan->value,
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'ketua.rt008@layananwarga.my.id',
            'name' => 'Ketua RT 008',
        ]);
    }

    public function test_store_normalizes_local_part_to_lowercase(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Sekretaris RT',
                'email_local' => 'Sekretaris.RT009',
                'password' => 'password123',
                'role' => UserRole::Kelurahan->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sekretaris.rt009@layananwarga.my.id',
        ]);
    }

    public function test_store_rejects_duplicate_local_part(): void
    {
        $admin = $this->createKelurahanUser();

        User::create([
            'name' => 'Existing',
            'email' => 'duplikat@layananwarga.my.id',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'Baru',
                'email_local' => 'duplikat',
                'password' => 'password123',
                'role' => UserRole::Kelurahan->value,
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors('email_local');
    }

    public function test_store_rejects_invalid_local_part(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'Invalid Email',
                'email_local' => 'bad email!',
                'password' => 'password123',
                'role' => UserRole::Kelurahan->value,
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors('email_local');
    }
}
