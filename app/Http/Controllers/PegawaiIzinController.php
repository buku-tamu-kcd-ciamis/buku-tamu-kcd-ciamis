<?php

namespace App\Http\Controllers;

use App\Models\PegawaiIzin;
use Illuminate\Http\Request;

class PegawaiIzinController extends Controller
{
    public function print($id)
    {
        $pegawai = PegawaiIzin::findOrFail($id);

        return view('print.surat-izin-pegawai', compact('pegawai'));
    }
}
