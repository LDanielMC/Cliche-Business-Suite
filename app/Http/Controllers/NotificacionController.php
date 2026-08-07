<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    /**
     * Lista completa (leídas y no leídas) del usuario autenticado.
     */
    public function index()
    {
        $notificaciones = Auth::user()->notifications()->paginate(20);

        return view('notificaciones.index', compact('notificaciones'));
    }

    /**
     * Marca una notificación como leída y redirige a la URL asociada.
     */
    public function marcarLeida(string $id)
    {
        $notificacion = Auth::user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        return redirect($notificacion->data['url'] ?? route('notificaciones.index'));
    }

    /**
     * Marca todas las notificaciones no leídas como leídas.
     */
    public function marcarTodasLeidas()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }

    /**
     * Elimina una notificación puntual del usuario autenticado.
     */
    public function destroy(string $id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();

        return back()->with('success', 'Notificación eliminada.');
    }

    /**
     * Elimina todas las notificaciones ya leídas del usuario autenticado.
     */
    public function eliminarLeidas()
    {
        Auth::user()->readNotifications()->delete();

        return back()->with('success', 'Notificaciones leídas eliminadas.');
    }
}
