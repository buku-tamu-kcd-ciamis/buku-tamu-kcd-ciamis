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

class PiketPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->id('piket')
      ->path('piket')
      ->login()
      ->profile(\App\Filament\Piket\Pages\EditProfile::class)
      ->darkMode(true)
      ->brandName('Cadisdik XIII — Piket')
      ->colors([
        'primary' => Color::Green,
      ])
      ->discoverResources(in: app_path('Filament/Piket/Resources'), for: 'App\\Filament\\Piket\\Resources')
      ->discoverPages(in: app_path('Filament/Piket/Pages'), for: 'App\\Filament\\Piket\\Pages')
      ->pages([
        \App\Filament\Piket\Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Piket/Widgets'), for: 'App\\Filament\\Piket\\Widgets')
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
      ])
      ->renderHook(
        'panels::head.end',
        fn() => '<link rel="stylesheet" href="' . asset('css/filament-custom.css') . '">'
      );
  }
}
