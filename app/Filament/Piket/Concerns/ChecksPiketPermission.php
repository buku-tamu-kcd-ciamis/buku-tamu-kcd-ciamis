<?php

namespace App\Filament\Piket\Concerns;

use App\Models\User;
use Filament\Facades\Filament;

trait ChecksPiketPermission
{
    protected static function hasPiketPermission(string $permission): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (!$user || !$user->hasRole('Piket')) {
            return false;
        }

        // Permission must come from the authenticated Piket user's own role.
        return (bool) ($user->role_user?->hasPermission($permission));
    }
}
