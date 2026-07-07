<?php

namespace App\Http\Controllers;

use App\Models\CalendarioFoto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CalendarioFotoController extends Controller
{
    public function index(Request $request)
    {
        $mes = (int) $request->input('mes', now()->month);
        $anio = (int) $request->input('anio', now()->year);

        $publicaciones = CalendarioFoto::with(['cliente.user'])
            ->whereYear('fecha_publicacion_programada', $anio)
            ->whereMonth('fecha_publicacion_programada', $mes)
            ->orderBy('fecha_publicacion_programada')
            ->get()
            ->groupBy(fn ($item) => $item->fecha_publicacion_programada->format('Y-m-d'));

        $semanas = $this->construirGrid($mes, $anio);
        $clientes = Cliente::activos()->with('user')->get();

        return view('calendario.index', compact('publicaciones', 'clientes', 'mes', 'anio', 'semanas'));
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
            'fecha_publicacion_programada' => ['required', 'date'],
            'fotografia_asociada' => ['required', 'image', 'max:5120'],
            'estatus' => ['required', 'in:' . implode(',', CalendarioFoto::ESTATUS)],
            'observaciones' => ['nullable', 'string'],
        ]);

        $validated['fotografia_asociada'] = $request->file('fotografia_asociada')
            ->store('calendario-fotos/' . $validated['cliente_id'], 'public');

        CalendarioFoto::create($validated + ['creado_por' => Auth::id()]);

        $fecha = \Carbon\Carbon::parse($validated['fecha_publicacion_programada']);

        return redirect()->route('calendario.index', ['mes' => $fecha->month, 'anio' => $fecha->year])
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
            'fecha_publicacion_programada' => ['required', 'date'],
            'fotografia_asociada' => ['nullable', 'image', 'max:5120'],
            'estatus' => ['required', 'in:' . implode(',', CalendarioFoto::ESTATUS)],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('fotografia_asociada')) {
            Storage::disk('public')->delete($calendario->fotografia_asociada);
            $validated['fotografia_asociada'] = $request->file('fotografia_asociada')
                ->store('calendario-fotos/' . $validated['cliente_id'], 'public');
        } else {
            unset($validated['fotografia_asociada']);
        }

        $calendario->update($validated);

        $fecha = \Carbon\Carbon::parse($validated['fecha_publicacion_programada']);

        return redirect()->route('calendario.index', ['mes' => $fecha->month, 'anio' => $fecha->year])
            ->with('success', 'Publicación actualizada correctamente.');
    }

    public function destroy(CalendarioFoto $calendario)
    {
        $mes = $calendario->fecha_publicacion_programada->month;
        $anio = $calendario->fecha_publicacion_programada->year;

        Storage::disk('public')->delete($calendario->fotografia_asociada);
        $calendario->delete();

        return redirect()->route('calendario.index', ['mes' => $mes, 'anio' => $anio])
            ->with('success', 'Publicación eliminada del calendario.');
    }

    /**
     * Construye la grilla de semanas (arreglos de 7 días, Lunes-Domingo)
     * para renderizar el calendario mensual como una tabla real.
     */
    private function construirGrid(int $mes, int $anio): array
    {
        $inicioMes = \Carbon\Carbon::create($anio, $mes, 1);
        $finMes = $inicioMes->copy()->endOfMonth();

        $inicioGrid = $inicioMes->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $finGrid = $finMes->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $semanas = [];
        $cursor = $inicioGrid->copy();

        while ($cursor->lte($finGrid)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $semana[] = [
                    'fecha' => $cursor->copy(),
                    'delMes' => $cursor->month === $mes,
                ];
                $cursor->addDay();
            }
            $semanas[] = $semana;
        }

        return $semanas;
    }
}
