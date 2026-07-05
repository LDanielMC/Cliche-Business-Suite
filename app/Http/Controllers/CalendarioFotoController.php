<?php

namespace App\Http\Controllers;

use App\Models\CalendarioFoto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarioFotoController extends Controller
{
    public function index(Request $request)
    {
        $mes = (int) $request->input('mes', now()->month);
        $anio = (int) $request->input('anio', now()->year);

        $publicaciones = CalendarioFoto::with(['cliente.user'])
            ->whereYear('fecha_publicacion', $anio)
            ->whereMonth('fecha_publicacion', $mes)
            ->orderBy('fecha_publicacion')
            ->get()
            ->groupBy(fn ($item) => $item->fecha_publicacion->format('Y-m-d'));

        $clientes = Cliente::activos()->with('user')->get();

        return view('calendario.index', compact('publicaciones', 'clientes', 'mes', 'anio'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('calendario.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha_publicacion' => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:' . implode(',', CalendarioFoto::ESTADOS)],
        ]);

        CalendarioFoto::create($validated + ['creado_por' => Auth::id()]);

        return redirect()->route('calendario.index', ['mes' => date('n', strtotime($validated['fecha_publicacion'])), 'anio' => date('Y', strtotime($validated['fecha_publicacion']))])
            ->with('success', 'Publicación agregada al calendario correctamente.');
    }

    public function edit(CalendarioFoto $calendario)
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('calendario.edit', ['publicacion' => $calendario, 'clientes' => $clientes]);
    }

    public function update(Request $request, CalendarioFoto $calendario)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha_publicacion' => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:' . implode(',', CalendarioFoto::ESTADOS)],
        ]);

        $calendario->update($validated);

        return redirect()->route('calendario.index', ['mes' => date('n', strtotime($validated['fecha_publicacion'])), 'anio' => date('Y', strtotime($validated['fecha_publicacion']))])
            ->with('success', 'Publicación actualizada correctamente.');
    }

    public function destroy(CalendarioFoto $calendario)
    {
        $mes = $calendario->fecha_publicacion->month;
        $anio = $calendario->fecha_publicacion->year;

        $calendario->delete();

        return redirect()->route('calendario.index', ['mes' => $mes, 'anio' => $anio])
            ->with('success', 'Publicación eliminada del calendario.');
    }
}
