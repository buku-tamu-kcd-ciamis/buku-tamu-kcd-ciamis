<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\UuidTrait;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements FilamentUser, HasAvatar
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
        if (!$this->role_user)
            return false;

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(['Super Admin', 'Kepala Cabang Dinas']),
            'piket' => $this->hasRole('Piket'),
            'staff' => $this->hasRole('Staff'),
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
     * Check if this user can be safely deleted.
     * Returns false if user is Super Admin or is the last user with their role.
     */
    public function isDeletable(): bool
    {
        // Super Admin cannot be deleted
        if ($this->hasRole('Super Admin')) {
            return false;
        }

        // Cannot delete if this is the last user with this role
        if ($this->role_user_id) {
            $othersWithSameRole = static::where('role_user_id', $this->role_user_id)
                ->where('id', '!=', $this->id)
                ->count();

            if ($othersWithSameRole === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Boot method â€” add deleting protection at model level.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if (!$user->isDeletable()) {
                return false;
            }
        });
    }

    /**
     * Get the dashboard route for this user's role
     */
    public function getDashboardRoute(): string
    {
        if (!$this->role_user)
            return '/';

        return match ($this->role_user->name) {
            'Super Admin' => '/admin',
            'Kepala Cabang Dinas' => '/admin',
            'Piket' => '/piket',
            'Staff' => '/staff',
            default => '/',
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
        'profile_photo_path',
        'role_user_id',
        'pegawai_id',
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

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function staffNotifications(): HasMany
    {
        return $this->hasMany(StaffNotification::class);
    }

    public function bookingChatsAsStaff(): HasMany
    {
        return $this->hasMany(BookingChat::class, 'staff_user_id');
    }

    public function bookingChatsAsPiket(): HasMany
    {
        return $this->hasMany(BookingChat::class, 'piket_user_id');
    }

    public function bookingChatMessages(): HasMany
    {
        return $this->hasMany(BookingChatMessage::class, 'sender_user_id');
    }

    public function webPushSubscriptions(): HasMany
    {
        return $this->hasMany(WebPushSubscription::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $rawPath = trim((string) ($this->profile_photo_path ?? ''));

        // Some chat queries only select id/name/email. Fallback to DB once so avatar still resolves.
        if ($rawPath === '' && $this->getKey()) {
            $freshPath = static::query()
                ->whereKey($this->getKey())
                ->value('profile_photo_path');

            $rawPath = trim((string) ($freshPath ?? ''));
        }

        if ($rawPath === '') {
            return null;
        }

        if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
            return $rawPath;
        }

        if (str_starts_with($rawPath, '/')) {
            return url($rawPath);
        }

        if (str_starts_with($rawPath, 'storage/')) {
            return asset($rawPath);
        }

        return Storage::url($rawPath);
    }
}
