<?php

namespace App\Filament\Staff\Widgets;

use App\Models\BukuTamu;
use App\Models\DropdownOption;
use App\Models\PegawaiIzin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StaffOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
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
        $piketHariIni = DropdownOption::where('category', DropdownOption::CATEGORY_PEGAWAI_PIKET)
            ->where('is_active', true)
            ->pluck('label')
            ->implode(', ');

        if (empty($piketHariIni)) {
            $piketHariIni = 'Belum ditentukan';
        }

        return [
            Stat::make('Izin Aktif', $izinAktif)
                ->description($izinAktif > 0 ? 'sedang berjalan' : 'tidak ada izin aktif')
                ->descriptionIcon($izinAktif > 0 ? 'heroicon-o-document-check' : 'heroicon-o-document')
                ->color($izinAktif > 0 ? 'warning' : 'gray'),
            Stat::make('Kunjungan Bulan Ini', $kunjunganBulanIni)
                ->description('tamu di bulan ' . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('Petugas Piket', '')
                ->description($piketHariIni)
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('success'),
        ];
    }
}
