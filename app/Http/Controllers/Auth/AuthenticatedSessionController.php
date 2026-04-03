<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect based on user role, but only honor intended URL in the same panel scope.
        $user = Auth::user();

        if (!$user instanceof User) {
            return redirect('/');
        }

        $redirect = $user->getDashboardRoute();
        $intendedUrl = (string) $request->session()->get('url.intended', '');
        $request->session()->forget('url.intended');
        $panelPath = Filament::getCurrentPanel()?->getPath();

        if ($intendedUrl !== '' && $panelPath) {
            $normalizedPath = '/' . ltrim($panelPath, '/');

            if (Str::startsWith($intendedUrl, $normalizedPath)) {
                return redirect()->to($intendedUrl);
            }
        }

        return redirect()->to($redirect);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
