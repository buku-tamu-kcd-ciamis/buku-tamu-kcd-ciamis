<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelAuthenticationRedirectTest extends TestCase
{
    public function test_guest_visiting_admin_panel_redirects_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $response->assertRedirect(route('filament.admin.auth.login'));
    }
}
