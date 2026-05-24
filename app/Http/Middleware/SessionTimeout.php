<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    protected int $timeout = 10;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $lastActivity = session('last_activity');
            $now = now()->timestamp;

            if ($lastActivity && ($now - $lastActivity) > ($this->timeout * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')
                    ->with('error', 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.');
            }

            session(['last_activity' => $now]);
        }

        return $next($request);
    }
}
