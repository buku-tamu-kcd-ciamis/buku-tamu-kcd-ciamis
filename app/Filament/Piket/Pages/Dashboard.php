<?php

namespace App\Filament\Piket\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
  protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
  protected static ?string $title = 'Dashboard';

  public function getWidgets(): array
  {
    return [
      \App\Filament\Piket\Widgets\StatsOverview::class,
      \App\Filament\Piket\Widgets\VisitChart::class,
    ];
  }
}
