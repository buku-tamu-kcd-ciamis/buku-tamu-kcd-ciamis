<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BukuTamuController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validasi data
            $validatedData = $request->validate([
                'jenis_id' => 'required|string',
                'nik' => 'required|string',
                'nama_lengkap' => 'required|string|max:255',
                'instansi' => 'nullable|string|max:255',
                'nomor_hp' => 'required|string|max:15',
                'jabatan' => 'nullable|string|max:255',
                'kabupaten_kota' => 'required|string|max:255',
                'bagian_dituju' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'keperluan' => 'required|string',
                'foto_selfie' => 'required|string',
                'foto_penerimaan' => 'nullable|string',
                'tanda_tangan' => 'required|string',
            ]);

            // Simpan data ke database
            BukuTamu::create($validatedData);

            return redirect()->route('index')->with('success', 'Data buku tamu berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Error saving buku tamu: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.')->withInput();
        }
    }
}
