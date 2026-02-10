<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuTamu extends Model
{
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
    ];
}
