<?php

namespace App\Http\Controllers;

use App\Models\PegawaiIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiIzinController extends Controller
{
    public function print($id)
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->role_user && $user->role_user->canPrint(),
            403,
            'Anda tidak memiliki izin untuk mencetak surat izin pegawai.'
        );

        $pegawai = PegawaiIzin::findOrFail($id);

        abort_unless($pegawai->isVerifiedByKcd(), 403, 'Data izin belum diverifikasi Kepala KCD, sehingga belum dapat dicetak.');

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

        $kepalaCabdin = \App\Models\PengaturanKcd::getSettings();

        return view('print.surat-izin-pegawai', compact('pegawai', 'kepalaCabdin'));
    }

    public function printBulk(Request $request)
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->role_user && $user->role_user->canPrint(),
            403,
            'Anda tidak memiliki izin untuk mencetak surat izin pegawai.'
        );

        $selectedIds = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn(string $id): int => (int) trim($id))
            ->filter(fn(int $id): bool => $id > 0)
            ->unique()
            ->values();

        $selectedList = $selectedIds->isNotEmpty()
            ? PegawaiIzin::query()->whereIn('id', $selectedIds->all())->orderBy('tanggal_mulai')->get()
            : collect();

        $pegawaiList = $selectedList
            ->filter(fn(PegawaiIzin $item): bool => $item->isVerifiedByKcd())
            ->values();

        $skippedCount = max(0, $selectedList->count() - $pegawaiList->count());

        if (Auth::check() && $pegawaiList->isNotEmpty()) {
            activity('pegawai_izin')
                ->causedBy(Auth::user())
                ->withProperties([
                    'jumlah_dicetak' => $pegawaiList->count(),
                    'jumlah_dilewati' => $skippedCount,
                    'ids_terpilih' => $selectedIds->all(),
                ])
                ->log('Mencetak bulk surat izin pegawai (' . $pegawaiList->count() . ' data)');
        }

        $kepalaCabdin = \App\Models\PengaturanKcd::getSettings();

        return view('print.surat-izin-pegawai-bulk', compact('pegawaiList', 'kepalaCabdin', 'skippedCount'));
    }
}
