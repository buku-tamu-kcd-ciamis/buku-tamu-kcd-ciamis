<?php

namespace App\Http\Controllers;

use App\Models\PegawaiIzin;
use App\Support\PegawaiIzinVerificationQr;
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

        $previewUrl = PegawaiIzinVerificationQr::signedPreviewUrl($pegawai);
        $previewQrDataUri = PegawaiIzinVerificationQr::signedPreviewQrDataUri($pegawai);

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

        return view('print.surat-izin-pegawai', compact('pegawai', 'kepalaCabdin', 'previewUrl', 'previewQrDataUri'));
    }

    public function preview(Request $request, $id)
    {
        $pegawai = PegawaiIzin::findOrFail($id);

        abort_unless($pegawai->isVerifiedByKcd(), 403, 'Data izin belum diverifikasi Kepala KCD, sehingga belum dapat dipratinjau.');

        $kepalaCabdin = \App\Models\PengaturanKcd::getSettings();
        $previewUrl = $request->fullUrl();
        $previewQrDataUri = PegawaiIzinVerificationQr::signedPreviewQrDataUri($pegawai);
        $isPreviewMode = true;

        return view('print.surat-izin-pegawai', compact('pegawai', 'kepalaCabdin', 'previewUrl', 'previewQrDataUri', 'isPreviewMode'));
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

        $previewUrls = [];
        $previewQrDataUris = [];

        foreach ($pegawaiList as $item) {
            $previewUrls[$item->id] = PegawaiIzinVerificationQr::signedPreviewUrl($item);
            $previewQrDataUris[$item->id] = PegawaiIzinVerificationQr::signedPreviewQrDataUri($item);
        }

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

        return view('print.surat-izin-pegawai-bulk', compact('pegawaiList', 'kepalaCabdin', 'skippedCount', 'previewUrls', 'previewQrDataUris'));
    }
}
