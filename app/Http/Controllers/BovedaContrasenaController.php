<?php

namespace App\Http\Controllers;

use App\Models\BovedaContrasena;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BovedaContrasenaController extends Controller
{
    public function index()
    {
        $credenciales = BovedaContrasena::with('cliente')->orderBy('nombre_plataforma')->paginate(10);

        return view('boveda.index', compact('credenciales'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('boveda.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'nombre_plataforma' => ['required', 'string', 'max:100'],
            'url_acceso' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
            'correo_asociado' => ['nullable', 'email', 'max:150'],
            'observaciones' => ['nullable', 'string'],
        ]);

        BovedaContrasena::create($validated + ['creado_por' => Auth::id()]);

        return redirect()->route('boveda.index')
            ->with('success', 'Credencial guardada correctamente.');
    }

    public function edit(BovedaContrasena $credencial)
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('boveda.edit', compact('credencial', 'clientes'));
    }

    public function update(Request $request, BovedaContrasena $credencial)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'nombre_plataforma' => ['required', 'string', 'max:100'],
            'url_acceso' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'max:255'],
            'correo_asociado' => ['nullable', 'email', 'max:150'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $credencial->update($validated);

        return redirect()->route('boveda.index')
            ->with('success', 'Credencial actualizada correctamente.');
    }

    public function destroy(BovedaContrasena $credencial)
    {
        $credencial->delete();

        return redirect()->route('boveda.index')
            ->with('success', 'Credencial eliminada correctamente.');
    }
}
