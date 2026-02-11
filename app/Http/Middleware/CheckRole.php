<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
  /**
   * Handle an incoming request.
   * Usage: middleware('role:Super Admin,Ketua KCD')
   */
  public function handle(Request $request, Closure $next, string ...$roles): Response
  {
    if (!$request->user()) {
      return redirect()->route('login');
    }

    if (!$request->user()->hasAnyRole($roles)) {
      abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    return $next($request);
  }
}
