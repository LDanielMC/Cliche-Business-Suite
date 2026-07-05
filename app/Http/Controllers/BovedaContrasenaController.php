<?php

namespace App\Http\Controllers;

use App\Models\BovedaContrasena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BovedaContrasenaController extends Controller
{
    public function index()
    {
        $credenciales = BovedaContrasena::orderBy('nombre_servicio')->paginate(10);

        return view('boveda.index', compact('credenciales'));
    }

    public function create()
    {
        return view('boveda.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_servicio' => ['required', 'string', 'max:150'],
            'url' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:255'],
        ]);

        BovedaContrasena::create($validated + ['creado_por' => Auth::id()]);

        return redirect()->route('boveda.index')
            ->with('success', 'Credencial guardada correctamente.');
    }

    public function edit(BovedaContrasena $credencial)
    {
        return view('boveda.edit', compact('credencial'));
    }

    public function update(Request $request, BovedaContrasena $credencial)
    {
        $validated = $request->validate([
            'nombre_servicio' => ['required', 'string', 'max:150'],
            'url' => ['nullable', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:255'],
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
