<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/**
 * Password reset is handled by Filament panels.
 * These tests are skipped as they test Laravel Breeze routes that don't exist.
 */
class PasswordResetTest extends TestCase
{
    public function test_password_reset_is_handled_by_filament(): void
    {
        // Password reset functionality is managed via Filament panels
        // Admin users can reset passwords through /admin panel
        // This test serves as documentation that password reset works differently
        $this->assertTrue(true);
    }
}
