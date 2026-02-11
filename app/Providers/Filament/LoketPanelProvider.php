<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class LoketPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->id('loket')
      ->path('loket')
      ->login()
      ->profile(\App\Filament\Loket\Pages\EditProfile::class)
      ->darkMode(true)
      ->brandName('Cadisdik XIII — Loket')
      ->colors([
        'primary' => Color::Green,
      ])
      ->discoverResources(in: app_path('Filament/Loket/Resources'), for: 'App\\Filament\\Loket\\Resources')
      ->discoverPages(in: app_path('Filament/Loket/Pages'), for: 'App\\Filament\\Loket\\Pages')
      ->pages([
        \App\Filament\Loket\Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Loket/Widgets'), for: 'App\\Filament\\Loket\\Widgets')
      ->widgets([
        Widgets\AccountWidget::class,
      ])
      ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        SubstituteBindings::class,
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
      ])
      ->authMiddleware([
        Authenticate::class,
      ]);
  }
}
