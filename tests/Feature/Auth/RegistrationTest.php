<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/**
 * Public registration is disabled in this application.
 * Users are created by Admin through the Filament admin panel.
 */
class RegistrationTest extends TestCase
{
    public function test_registration_is_disabled_for_public(): void
    {
        // No public registration - users are created by admins via /admin/user
        $response = $this->get('/register');

        // Should return 404 since registration route doesn't exist
        $response->assertStatus(404);
    }
}
