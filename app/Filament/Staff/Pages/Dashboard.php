<?php

namespace App\Filament\Staff\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Staff\Widgets\StaffNotificationWidget::class,
        ];
    }
}
