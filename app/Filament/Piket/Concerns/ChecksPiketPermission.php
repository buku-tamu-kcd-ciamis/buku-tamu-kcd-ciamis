<?php

namespace App\Filament\Piket\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait ChecksPiketPermission
{
    protected static function hasPiketPermission(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('Piket')) {
            return false;
        }

        // Permission must come from the authenticated Piket user's own role.
        return (bool) ($user->role_user?->hasPermission($permission));
    }
}
