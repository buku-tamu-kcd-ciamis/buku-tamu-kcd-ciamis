<?php

namespace App\Filament\Staff\Widgets;

use App\Models\StaffNotification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StaffNotificationWidget extends BaseWidget
{
    protected ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        $userId = Auth::id();

        $unread = StaffNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        $pending = StaffNotification::where('user_id', $userId)
            ->whereNull('response')
            ->count();

        $todayTotal = StaffNotification::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        $accepted = StaffNotification::where('user_id', $userId)
            ->where('response', 'diterima')
            ->whereDate('created_at', today())
            ->count();

        return [
            Stat::make('Notifikasi Baru', $unread)
                ->description('Belum dibaca')
                ->descriptionIcon('heroicon-o-envelope')
                ->color($unread > 0 ? 'danger' : 'gray')
                ->chart([$unread > 0 ? 1 : 0, $unread]),
            Stat::make('Menunggu Respons', $pending)
                ->description('Belum dijawab')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),
            Stat::make('Tamu Hari Ini', $todayTotal)
                ->description("{$accepted} diterima")
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
        ];
    }
}
