<?php

namespace App\Filament\Staff\Widgets;

use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Models\PegawaiIzin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;

class StaffOverviewWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Filament::auth()->user();
        $pegawai = $user->pegawai;
        $pegawaiNama = $pegawai?->nama ?? $user->name;

        // Active permits (disetujui / aktif)
        $izinAktif = PegawaiIzin::where('nama_pegawai', $pegawaiNama)
            ->whereIn('status', ['disetujui', 'aktif'])
            ->where('tanggal_selesai', '>=', now()->toDateString())
            ->count();

        // My visits this month (from buku_tamu where staff_dituju matches)
        $kunjunganBulanIni = BukuTamu::where('staff_dituju', $pegawaiNama)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Petugas Piket hari ini (from DropdownOption)
        $piketHariIniList = DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)
            ->where('is_active', true)
            ->pluck('label')
            ->filter()
            ->values();

        $totalPiket = $piketHariIniList->count();

        $piketHariIni = $totalPiket > 0
            ? 'Petugas piket aktif hari ini'
            : 'Belum ditentukan';

        return [
            Stat::make('Izin Aktif', $izinAktif)
                ->description($izinAktif > 0 ? 'sedang berjalan' : 'tidak ada izin aktif')
                ->color($izinAktif > 0 ? 'warning' : 'gray'),
            Stat::make('Kunjungan Bulan Ini', $kunjunganBulanIni)
                ->description('tamu di bulan ' . now()->translatedFormat('F'))
                ->color('primary'),
            Stat::make('Petugas Piket', (string) $totalPiket)
                ->description($piketHariIni)
                ->extraAttributes(['class' => 'staff-piket-stat'])
                ->color('success'),
        ];
    }
}
