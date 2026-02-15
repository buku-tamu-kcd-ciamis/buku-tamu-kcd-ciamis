<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory, UuidTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'need_approval',
        'author_id',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
        'need_approval' => 'boolean',
    ];

    public const NEED_APPROVAL = true;
    public const NOT_NEED_APPROVAL = false;
    public const APPROVE_STATUS = [
        self::NEED_APPROVAL => 'Need Approve',
        self::NOT_NEED_APPROVAL => 'No Need Approve'
    ];

    /**
     * Default permissions for new roles.
     */
    public static function getDefaultPermissions(): array
    {
        return [
            'buku_tamu' => false,
            'activity_log' => false,
            'pegawai_izin' => false,
            'data_pegawai' => false,
            'dropdown_options' => false,
            'pegawai_piket' => false,
            'user_management' => false,
            'can_print' => false,
            'can_change_status' => false,
        ];
    }

    /**
     * Resource permission keys with labels for the settings page.
     */
    public static function getResourcePermissionLabels(): array
    {
        return [
            'buku_tamu' => 'Buku Tamu',
            'activity_log' => 'Log Aktivitas',
            'pegawai_izin' => 'Izin Pegawai',
            'data_pegawai' => 'Data Pegawai',
            'dropdown_options' => 'Manajemen Buku Tamu',
            'pegawai_piket' => 'Data Pegawai Piket',
            'user_management' => 'Manajemen User',
        ];
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $key): bool
    {
        // Super Admin always has all permissions
        if ($this->name === 'Super Admin') {
            return true;
        }

        $permissions = $this->permissions ?? self::getDefaultPermissions();
        return $permissions[$key] ?? false;
    }

    /**
     * Check if this role can print.
     */
    public function canPrint(): bool
    {
        return $this->hasPermission('can_print');
    }

    /**
     * Check if this role can change status.
     */
    public function canChangeStatus(): bool
    {
        return $this->hasPermission('can_change_status');
    }

    // public function user() : HasMany
    // {
    //     return $this->hasMany(User::class);
    // }
}
