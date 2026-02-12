<?php

namespace App\Filament\Piket\Widgets;

use App\Models\BukuTamu;
use App\Models\PegawaiIzin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
  protected static ?int $sort = 1;

  protected function getStats(): array
  {
    $today = now()->toDateString();

    return [
      Stat::make('Tamu Hari Ini', BukuTamu::whereDate('created_at', $today)->count())
        ->icon('heroicon-o-users')
        ->color('success'),

      Stat::make('Menunggu', BukuTamu::where('status', 'menunggu')->count())
        ->icon('heroicon-o-clock')
        ->color('warning'),

      Stat::make('Diproses', BukuTamu::where('status', 'diproses')->count())
        ->icon('heroicon-o-arrow-path')
        ->color('info'),

      Stat::make('Selesai Hari Ini', BukuTamu::whereDate('created_at', $today)->where('status', 'selesai')->count())
        ->icon('heroicon-o-check-circle')
        ->color('success'),

      Stat::make('Ditolak Hari Ini', BukuTamu::whereDate('created_at', $today)->where('status', 'ditolak')->count())
        ->icon('heroicon-o-x-circle')
        ->color('danger'),

      Stat::make('Pegawai Izin Aktif', PegawaiIzin::where('status', 'aktif')
        ->where('tanggal_mulai', '<=', $today)
        ->where('tanggal_selesai', '>=', $today)
        ->count())
        ->icon('heroicon-o-user-minus')
        ->color('gray'),
    ];
  }
}
