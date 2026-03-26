<?php

namespace App\Filament\Staff\Widgets;

use App\Filament\Staff\Pages\NotifikasiTamu;
use App\Filament\Staff\Pages\RiwayatKunjungan;
use App\Filament\Staff\Resources\PegawaiIzinResource;
use App\Models\BukuTamu;
use App\Models\PegawaiIzin;
use App\Models\StaffNotification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StaffQuickActionWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pegawaiNama = $user?->pegawai?->nama ?? $user?->name;

        $notifikasiBelumDibaca = StaffNotification::where('user_id', $user?->id)
            ->where('is_read', false)
            ->count();

        $izinMenunggu = PegawaiIzin::where('nama_pegawai', $pegawaiNama)
            ->where('status', PegawaiIzin::STATUS_MENUNGGU)
            ->count();

        $riwayatKunjungan = BukuTamu::where('staff_dituju', $pegawaiNama)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Aksi Cepat: Notifikasi', $notifikasiBelumDibaca)
                ->description('buka notifikasi tamu')
                ->url(NotifikasiTamu::getUrl())
                ->openUrlInNewTab(false)
                ->extraAttributes(['class' => 'staff-quick-action'])
                ->color($notifikasiBelumDibaca > 0 ? 'danger' : 'primary'),

            Stat::make('Aksi Cepat: Izin Saya', $izinMenunggu)
                ->description('pengajuan menunggu persetujuan')
                ->url(PegawaiIzinResource::getUrl('index'))
                ->openUrlInNewTab(false)
                ->extraAttributes(['class' => 'staff-quick-action'])
                ->color($izinMenunggu > 0 ? 'warning' : 'primary'),

            Stat::make('Aksi Cepat: Riwayat', $riwayatKunjungan)
                ->description('kunjungan bulan ini')
                ->url(RiwayatKunjungan::getUrl())
                ->openUrlInNewTab(false)
                ->extraAttributes(['class' => 'staff-quick-action'])
                ->color('success'),
        ];
    }
}
