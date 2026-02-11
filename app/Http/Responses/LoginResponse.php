<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
  public function toResponse($request)
  {
    $user = auth()->user();

    // Redirect based on user role
    $url = $user->getDashboardRoute();

    return redirect()->intended($url);
  }
}
