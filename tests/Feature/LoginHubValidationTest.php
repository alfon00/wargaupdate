<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginHubValidationTest extends TestCase
{
    public function test_login_hub_requires_email_and_password(): void
    {
        $response = $this->post(route('login.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'Email wajib diisi.',
            'password' => 'Kata sandi wajib diisi.',
        ]);
    }
}

