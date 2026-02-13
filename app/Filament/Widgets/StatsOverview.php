<?php

namespace App\Filament\Widgets;

use App\Models\BukuTamu;
use App\Models\PegawaiIzin;
use App\Models\RoleUser;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
  protected static ?int $sort = 1;

  protected function getStats(): array
  {
    $today = now()->toDateString();
    $thisMonth = now()->startOfMonth();
    $thisWeek = now()->subDays(6);

    return [
      // Prioritas 1: Monitoring Langsung
      Stat::make('Tamu Hari Ini', BukuTamu::whereDate('created_at', $today)->count())
        ->icon('heroicon-o-users')
        ->description('Kunjungan hari ini')
        ->color('gray'),

      Stat::make('Menunggu', BukuTamu::where('status', 'menunggu')->count())
        ->icon('heroicon-o-clock')
        ->description('Perlu diproses')
        ->color('gray'),

      Stat::make('Diproses', BukuTamu::where('status', 'diproses')->count())
        ->icon('heroicon-o-arrow-path')
        ->description('Sedang berlangsung')
        ->color('gray'),

      Stat::make('Selesai Hari Ini', BukuTamu::whereDate('created_at', $today)->where('status', 'selesai')->count())
        ->icon('heroicon-o-check-circle')
        ->description('Selesai dilayani')
        ->color('gray'),

      Stat::make('Ditolak Hari Ini', BukuTamu::whereDate('created_at', $today)->where('status', 'ditolak')->count())
        ->icon('heroicon-o-x-circle')
        ->description('Tidak dapat dilayani')
        ->color('gray'),

      Stat::make('Pegawai Izin Aktif', PegawaiIzin::where('status', 'aktif')
        ->where('tanggal_mulai', '<=', $today)
        ->where('tanggal_selesai', '>=', $today)
        ->count())
        ->icon('heroicon-o-user-minus')
        ->description('Sedang izin/cuti')
        ->color('gray'),

      // Prioritas 2: Trend & Statistik
      Stat::make('Tamu Minggu Ini', BukuTamu::where('created_at', '>=', $thisWeek)->count())
        ->icon('heroicon-o-chart-bar')
        ->description('7 hari terakhir')
        ->color('gray'),

      Stat::make('Tamu Bulan Ini', BukuTamu::where('created_at', '>=', $thisMonth)->count())
        ->icon('heroicon-o-calendar')
        ->description('Bulan ' . now()->translatedFormat('F'))
        ->color('gray'),

      Stat::make('Total Tamu', BukuTamu::count())
        ->icon('heroicon-o-user-group')
        ->description('Semua kunjungan')
        ->color('gray'),

      // Prioritas 3: Management Sistem
      Stat::make('Total User', User::count())
        ->icon('heroicon-o-user-circle')
        ->description('User sistem')
        ->color('gray'),

      Stat::make('Total Role', RoleUser::count())
        ->icon('heroicon-o-shield-check')
        ->description('Role pengguna')
        ->color('gray'),

      Stat::make('Log Aktivitas', DB::table('activity_log')->whereDate('created_at', $today)->count())
        ->icon('heroicon-o-document-text')
        ->description('Aktivitas hari ini')
        ->color('gray'),
    ];
  }
}
