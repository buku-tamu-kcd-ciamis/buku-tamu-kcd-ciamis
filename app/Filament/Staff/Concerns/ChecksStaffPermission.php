<?php

namespace App\Filament\Staff\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait ChecksStaffPermission
{
    protected static function hasStaffPermission(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('Staff')) {
            return false;
        }

        return (bool) ($user?->role_user?->hasPermission($permission));
    }

    /**
     * @param array<int, string> $permissions
     */
    protected static function hasAnyStaffPermission(array $permissions): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('Staff') || !$user->role_user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->role_user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}