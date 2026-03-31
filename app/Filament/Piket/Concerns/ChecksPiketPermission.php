<?php

namespace App\Filament\Piket\Concerns;

use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait ChecksPiketPermission
{
    protected static function hasPiketPermission(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // In Piket panel, check permission from Piket role settings
        $piketRole = RoleUser::query()->where('name', 'Piket')->first();

        if ($piketRole) {
            // Use hasPermission method which handles all edge cases and defaults
            return $piketRole->hasPermission($permission);
        }

        // Fallback to user's role permission
        return (bool) ($user->role_user?->hasPermission($permission));
    }
}
