<?php

namespace App\Http\Controllers;

use App\Models\PegawaiIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiIzinController extends Controller
{
    public function print($id)
    {
        $pegawai = PegawaiIzin::findOrFail($id);

        // Log aktivitas mencetak surat
        $activity = activity('pegawai_izin')
            ->performedOn($pegawai)
            ->withProperties([
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama_pegawai,
                'jenis_izin' => $pegawai->jenis_izin,
            ]);

        if (Auth::check()) {
            $activity->causedBy(Auth::user());
        }

        $activity->log("Mencetak surat izin {$pegawai->jenis_izin} atas nama {$pegawai->nama_pegawai}");

        return view('print.surat-izin-pegawai', compact('pegawai'));
    }
}
