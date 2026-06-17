<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicSuccessPageTest extends TestCase
{
    public function test_pendataan_ulang_success_redirects_without_session(): void
    {
        $this->get(route('services.pendataan-ulang.success'))
            ->assertRedirect(route('services.pendataan-ulang'))
            ->assertSessionHas('info');
    }

    public function test_contact_success_redirects_without_session(): void
    {
        $this->get(route('contact.success'))
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('info');
    }
}
