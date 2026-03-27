<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Concerns\ChecksStaffPermission;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use ChecksStaffPermission;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';

    public static function canAccess(): bool
    {
        return static::hasAnyStaffPermission([
            'buku_tamu',
            'pegawai_izin',
            'riwayat_tamu',
            'data_pegawai',
        ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Staff\Widgets\StaffNotificationWidget::class,
            \App\Filament\Staff\Widgets\StaffQuickActionWidget::class,
            \App\Filament\Staff\Widgets\StaffOverviewWidget::class,
            \App\Filament\Staff\Widgets\StaffVisitTrendWidget::class,
        ];
    }
}
