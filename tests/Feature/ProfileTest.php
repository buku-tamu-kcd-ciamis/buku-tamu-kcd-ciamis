<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Profile management is handled by Filament panels.
 * Admin profile: /admin/profile (requires Super Admin or Kepala Cabang Dinas role)
 * Piket profile: /piket/profile (requires Piket or Super Admin role)
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_admin_profile_page_requires_authentication(): void
    {
        $response = $this->get('/admin/profile');

        // Should redirect to login when not authenticated
        $response->assertRedirect('/admin/login');
    }

    public function test_filament_piket_profile_page_requires_authentication(): void
    {
        $response = $this->get('/piket/profile');

        // Should redirect to login when not authenticated
        $response->assertRedirect('/piket/login');
    }

    public function test_admin_user_can_access_admin_profile(): void
    {
        // Create Super Admin role
        $adminRole = RoleUser::create([
            'name' => 'Super Admin',
            'need_approval' => false,
            'permissions' => RoleUser::getDefaultPermissions(),
        ]);

        $user = User::factory()->create([
            'role_user_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($user)->get('/admin/profile');

        $response->assertOk();
    }

    public function test_piket_user_can_access_piket_profile(): void
    {
        // UserFactory creates user with Piket role by default
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/piket/profile');

        $response->assertOk();
    }

    public function test_piket_user_cannot_access_admin_profile(): void
    {
        // UserFactory creates user with Piket role
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/profile');

        // Piket users are forbidden from accessing admin panel
        $response->assertForbidden();
    }
}
