<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BukuTamu extends Model
{
    use LogsActivity;

    protected $table = 'buku_tamu';

    protected $fillable = [
        'jenis_id',
        'nik',
        'nama_lengkap',
        'instansi',
        'nomor_hp',
        'jabatan',
        'kabupaten_kota',
        'bagian_dituju',
        'email',
        'keperluan',
        'foto_selfie',
        'foto_penerimaan',
        'tanda_tangan',
        'status',
        'catatan',
        'nama_penerima',
    ];

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUS_LABELS = [
        'menunggu' => 'Menunggu',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'catatan', 'nama_penerima'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Buku tamu {$eventName}")
            ->useLogName('buku_tamu');
    }
}
