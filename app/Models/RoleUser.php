<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class RoleUser extends Model
{
    use HasFactory, UuidTrait, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'permissions', 'need_approval'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('role')
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Role ditambahkan: ' . ($this->name ?? ''),
                'updated' => 'Role diperbarui: ' . ($this->name ?? ''),
                'deleted' => 'Role dihapus: ' . ($this->name ?? ''),
                default => "Role {$eventName}",
            });
    }

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
            'rekap_izin' => false,
            'data_pegawai' => false,
            'dropdown_options' => false,
            'pegawai_piket' => false,
            'user_management' => false,
            'profil_kepala_cabdin' => false,
            'riwayat_tamu' => false,
            'pengantar_berkas' => false,
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
            'rekap_izin' => 'Rekap Izin Pegawai',
            'data_pegawai' => 'Data Pegawai',
            'dropdown_options' => 'Manajemen Buku Tamu',
            'pegawai_piket' => 'Data Pegawai Piket',
            'user_management' => 'Manajemen User',
            'profil_kepala_cabdin' => 'Profil Kepala Cabang Dinas',
            'riwayat_tamu' => 'Riwayat Pengunjung',
            'pengantar_berkas' => 'Pengantar Berkas',
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

        $permissions = $this->permissions;

        if (!is_array($permissions) || $permissions === []) {
            $permissions = self::getSeedPermissionsForRole($this->name);
        }

        return $permissions[$key] ?? false;
    }

    /**
     * Fallback permissions by role name for legacy rows with null permissions.
     */
    public static function getSeedPermissionsForRole(?string $roleName): array
    {
        $defaults = self::getDefaultPermissions();

        return match ($roleName) {
            'Super Admin' => array_fill_keys(array_keys($defaults), true),
            'Piket' => array_replace($defaults, [
                'buku_tamu' => true,
                'pegawai_izin' => true,
                'riwayat_tamu' => true,
                'pengantar_berkas' => true,
                'can_print' => true,
                'can_change_status' => true,
            ]),
            'Kepala Cabang Dinas' => array_replace($defaults, [
                'buku_tamu' => true,
                'pegawai_izin' => true,
                'rekap_izin' => true,
                'riwayat_tamu' => true,
                'pengantar_berkas' => true,
                'can_print' => true,
                'can_change_status' => true,
            ]),
            'Staff' => array_replace($defaults, [
                'buku_tamu' => true,
                'pegawai_izin' => true,
                'data_pegawai' => true,
                'riwayat_tamu' => true,
            ]),
            default => $defaults,
        };
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


