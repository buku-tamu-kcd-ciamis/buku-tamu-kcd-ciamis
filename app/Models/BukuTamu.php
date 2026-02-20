<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Helpers\ImageHelper;

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

    protected $appends = ['foto_selfie_url', 'foto_penerimaan_url', 'tanda_tangan_url'];

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

    /**
     * Accessor: Resolve foto_selfie ke URL (kompatibel Base64 & file path)
     */
    public function getFotoSelfieUrlAttribute(): ?string
    {
        return ImageHelper::resolveUrl($this->attributes['foto_selfie'] ?? null);
    }

    /**
     * Accessor: Resolve foto_penerimaan ke URL (kompatibel Base64 & file path)
     */
    public function getFotoPenerimaanUrlAttribute(): ?string
    {
        return ImageHelper::resolveUrl($this->attributes['foto_penerimaan'] ?? null);
    }

    /**
     * Accessor: Resolve tanda_tangan ke URL (kompatibel Base64 & file path)
     */
    public function getTandaTanganUrlAttribute(): ?string
    {
        return ImageHelper::resolveUrl($this->attributes['tanda_tangan'] ?? null);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'catatan', 'nama_penerima'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => "Mendaftarkan tamu baru atas nama {$this->nama_lengkap}",
                'updated' => "Memperbarui status buku tamu atas nama {$this->nama_lengkap}",
                'deleted' => "Menghapus data buku tamu atas nama {$this->nama_lengkap}",
                default => "Aktivitas buku tamu: {$eventName}",
            })
            ->useLogName('buku_tamu');
    }
}
