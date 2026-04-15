<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (!$user instanceof User) {
            $user = Auth::user();
        }

        $fallbackUrl = $user instanceof User
            ? $user->getDashboardRoute()
            : '/';

        $intendedUrl = (string) $request->session()->get('url.intended', '');
        $request->session()->forget('url.intended');
        $panelPath = Filament::getCurrentPanel()?->getPath();

        if ($intendedUrl !== '' && $panelPath) {
            $normalizedPath = '/' . ltrim($panelPath, '/');
            $intendedPath = (string) parse_url($intendedUrl, PHP_URL_PATH);

            if ($intendedPath === '') {
                $intendedPath = $intendedUrl;
            }

            if (Str::startsWith($intendedPath, $normalizedPath)) {
                return redirect()->to($intendedUrl);
            }
        }

        return redirect()->to($fallbackUrl);
    }
}
