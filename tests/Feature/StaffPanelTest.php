<?php

namespace Tests\Feature;

use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPanelTest extends TestCase
{
    use RefreshDatabase;

    private function createStaffUser(): User
    {
        $staffRole = RoleUser::create([
            'name' => 'Staff',
            'need_approval' => false,
            'permissions' => RoleUser::getDefaultPermissions(),
        ]);

        /** @var User $user */
        $user = User::factory()->create([
            'role_user_id' => $staffRole->id,
        ]);

        return $user;
    }

    public function test_filament_staff_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/staff/login');

        $response->assertOk();
    }

    public function test_filament_staff_profile_requires_authentication(): void
    {
        $response = $this->get('/staff/profile');

        $response->assertRedirect('/staff/login');
    }

    public function test_staff_user_can_access_staff_profile(): void
    {
        $user = $this->createStaffUser();

        $response = $this->actingAs($user)->get('/staff/profile');

        $response->assertOk();
    }

    public function test_staff_user_cannot_access_admin_profile(): void
    {
        $user = $this->createStaffUser();

        $response = $this->actingAs($user)->get('/admin/profile');

        $response->assertForbidden();
    }

    public function test_staff_user_cannot_access_piket_profile(): void
    {
        $user = $this->createStaffUser();

        $response = $this->actingAs($user)->get('/piket/profile');

        $response->assertForbidden();
    }
}
