<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\VisitChart;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
  protected static ?string $navigationIcon = 'heroicon-o-home';
  protected static ?string $navigationLabel = 'Dashboard Admin';
  protected static ?string $title = 'Dashboard Admin';

  public function getWidgets(): array
  {
    /** @var User $user */
    $user = Auth::user();

    // Ketua KCD melihat dashboard seperti Piket (hanya monitoring)
    if ($user && $user->hasRole('Ketua KCD')) {
      return [
        \App\Filament\Piket\Widgets\StatsOverview::class,
        \App\Filament\Piket\Widgets\VisitChart::class,
      ];
    }

    // Super Admin melihat dashboard lengkap
    return [
      StatsOverview::class,
      VisitChart::class,
    ];
  }
}
