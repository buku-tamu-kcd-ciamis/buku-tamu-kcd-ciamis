<?php

namespace App\Filament\Loket\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
  protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
  protected static ?string $title = 'Dashboard Loket';

  public function getWidgets(): array
  {
    return [
      \App\Filament\Loket\Widgets\StatsOverview::class,
      \App\Filament\Loket\Widgets\VisitChart::class,
    ];
  }
}
