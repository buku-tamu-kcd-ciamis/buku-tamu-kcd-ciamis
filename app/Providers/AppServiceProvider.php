<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override Filament's LoginResponse to redirect by user role
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Detect ngrok or other proxies
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Notifikasi mengambang di atas tengah (seperti halaman publik)
        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::Start);

        // Log aktivitas login
        Event::listen(Login::class, function (Login $event) {
            /** @var \App\Models\User $user */
            $user = $event->user;
            $roleName = $user->role_user?->name ?? 'Unknown';

            activity('auth')
                ->causedBy($user)
                ->withProperties([
                    'user_name' => $user->name,
                    'role' => $roleName,
                    'email' => $user->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log("Login ke sistem sebagai {$roleName}");
        });

        // Log aktivitas logout
        Event::listen(Logout::class, function (Logout $event) {
            /** @var \App\Models\User|null $user */
            $user = $event->user;
            if ($user) {
                $roleName = $user->role_user?->name ?? 'Unknown';

                activity('auth')
                    ->causedBy($user)
                    ->withProperties([
                        'user_name' => $user->name,
                        'role' => $roleName,
                        'email' => $user->email,
                        'ip_address' => request()->ip(),
                    ])
                    ->log("Logout dari sistem sebagai {$roleName}");
            }
        });
    }
}
