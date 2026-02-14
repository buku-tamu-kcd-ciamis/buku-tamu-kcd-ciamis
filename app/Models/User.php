<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\UuidTrait;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, UuidTrait, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role_user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "User baru '{$this->name}' berhasil dibuat",
                'updated' => "Data user '{$this->name}' diperbarui",
                'deleted' => "User '{$this->name}' dihapus",
                default => "User '{$this->name}' {$eventName}",
            });
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = ['role_user'];

    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->role_user) return false;

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(['Super Admin', 'Ketua KCD']),
            'piket' => $this->hasAnyRole(['Piket', 'Super Admin']),
            default => false,
        };
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role_user && $this->role_user->name === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->role_user && in_array($this->role_user->name, $roles);
    }

    /**
     * Get the dashboard route for this user's role
     */
    public function getDashboardRoute(): string
    {
        if (!$this->role_user) return '/';

        return match ($this->role_user->name) {
            'Super Admin' => '/admin',
            'Ketua KCD'   => '/admin',
            'Piket'       => '/piket',
            default       => '/',
        };
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function role_user(): BelongsTo
    {
        return $this->belongsTo(RoleUser::class);
    }
}
