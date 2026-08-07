<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        // Clientes suspendidos: acceso angosto solo a las tres rutas de renovación/pago.
        // dado_de_baja e inactivo siguen totalmente bloqueados.
        if ($user->estatus === User::ESTATUS_SUSPENDIDO && $user->role === User::ROLE_CLIENTE) {
            $rutasPermitidas = [
                'renovaciones.cliente.index',
                'renovaciones.cliente.renovar',
                'renovaciones.cliente.enviar',
                'renovaciones.cliente.solicitar',
            ];

            if (!in_array($request->route()?->getName(), $rutasPermitidas)) {
                return redirect()->route('renovaciones.cliente.index')
                    ->with('error', 'Tu cuenta está suspendida. Solo puedes acceder al módulo de renovaciones para regularizarla.');
            }

            return $next($request);
        }

        // Revocar acceso inmediato si la cuenta fue dada de baja o está inactiva
        if ($user->estatus !== User::ESTATUS_ACTIVO) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')
                ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}
