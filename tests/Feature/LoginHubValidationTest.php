<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginHubValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_hub_requires_email_and_password(): void
    {
        $response = $this->post(route('login.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Email wajib diisi.',
            'password' => 'Kata sandi wajib diisi.',
        ]);
    }

    public function test_kelurahan_account_cannot_login_via_hub(): void
    {
        User::create([
            'name' => 'Monitoring Kelurahan',
            'email' => 'kelurahan-login@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->post(route('login.store'), [
            'email' => 'kelurahan-login@test.local',
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}

