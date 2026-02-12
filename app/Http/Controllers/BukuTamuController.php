<?php

namespace App\Http\Controllers;

use App\Models\BukuTamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BukuTamuController extends Controller
{
    /**
     * Get guest data by NIK for auto-fill
     */
    public function getByNik(Request $request)
    {
        $nik = $request->query('nik');

        if (!$nik) {
            return response()->json(['found' => false]);
        }

        // Cari data tamu terakhir dengan NIK ini
        $guest = BukuTamu::where('nik', $nik)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$guest) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'data' => [
                'nama_lengkap' => $guest->nama_lengkap,
                'instansi' => $guest->instansi,
                'nomor_hp' => $guest->nomor_hp,
                'jabatan' => $guest->jabatan,
                'kabupaten_kota' => $guest->kabupaten_kota,
                'email' => $guest->email,
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validasi data
            $validatedData = $request->validate([
                'jenis_id' => 'required|string',
                'nik' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        // Check for repeated digits (more than 3 consecutive same digits)
                        if (preg_match('/(\d)\1{3,}/', $value)) {
                            $fail('NIK tidak valid. Angka tidak boleh sama lebih dari 3 digit berturut-turut.');
                        }

                        // Check for sequential digits (more than 2 consecutive sequential digits)
                        for ($i = 0; $i < strlen($value) - 2; $i++) {
                            $digit1 = (int)$value[$i];
                            $digit2 = (int)$value[$i + 1];
                            $digit3 = (int)$value[$i + 2];

                            // Check ascending (123, 234, etc)
                            if ($digit2 === $digit1 + 1 && $digit3 === $digit2 + 1) {
                                $fail('NIK tidak valid. Angka tidak boleh berurutan lebih dari 2 digit.');
                                break;
                            }

                            // Check descending (321, 432, etc)
                            if ($digit2 === $digit1 - 1 && $digit3 === $digit2 - 1) {
                                $fail('NIK tidak valid. Angka tidak boleh berurutan lebih dari 2 digit.');
                                break;
                            }
                        }
                    },
                ],
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

    /**
     * Print surat pengantar / detail kunjungan
     */
    public function print($id)
    {
        $tamu = BukuTamu::findOrFail($id);
        return view('print.buku-tamu', compact('tamu'));
    }

    /**
     * Print bulk / filtered kunjungan
     */
    public function printBulk(Request $request)
    {
        $query = BukuTamu::query()->where('status', 'selesai');

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('nama') && $request->nama) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama . '%');
        }

        if ($request->has('keperluan') && $request->keperluan) {
            $query->where('keperluan', 'like', '%' . $request->keperluan . '%');
        }

        if ($request->has('type') && $request->type === 'pengantar') {
            $query->where(function ($q) {
                $q->where('keperluan', 'like', '%berkas%')
                    ->orWhere('keperluan', 'like', '%surat%')
                    ->orWhere('keperluan', 'like', '%dokumen%')
                    ->orWhere('keperluan', 'like', '%legalisir%');
            });
        }

        $tamuList = $query->orderBy('created_at', 'desc')->get();

        return view('print.buku-tamu-bulk', compact('tamuList'));
    }
}
