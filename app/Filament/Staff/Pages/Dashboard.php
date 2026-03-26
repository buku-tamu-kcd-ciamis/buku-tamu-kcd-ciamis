<?php

namespace App\Filament\Staff\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';

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
