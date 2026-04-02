<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = auth()->user();
        $fallbackUrl = $user?->getDashboardRoute() ?? '/';

        $intendedUrl = (string) $request->session()->get('url.intended', '');
        $request->session()->forget('url.intended');
        $panelPath = Filament::getCurrentPanel()?->getPath();

        if ($intendedUrl !== '' && $panelPath) {
            $normalizedPath = '/' . ltrim($panelPath, '/');

            if (Str::startsWith($intendedUrl, $normalizedPath)) {
                return redirect()->to($intendedUrl);
            }
        }

        return redirect()->to($fallbackUrl);
    }
}
