<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\User;
use Illuminate\Http\Request;

class RenovacionController extends Controller
{
    public function index()
    {
        $renovaciones = ControlRenovacion::with('cliente.user')
            ->orderBy('fecha_vencimiento')
            ->paginate(10);

        return view('renovaciones.index', compact('renovaciones'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->with('user')
            ->whereDoesntHave('renovaciones', fn ($q) => $q->whereIn('estatus', [ControlRenovacion::ESTATUS_VIGENTE, ControlRenovacion::ESTATUS_POR_VENCER]))
            ->get();

        return view('renovaciones.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha_inicio' => ['required', 'date'],
            'meses' => ['required', 'integer', 'min:1', 'max:12'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $fechaInicio = \Carbon\Carbon::parse($validated['fecha_inicio']);
        $fechaVencimiento = $fechaInicio->copy()->addMonths((int) $validated['meses']);

        ControlRenovacion::create([
            'cliente_id' => $validated['cliente_id'],
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento,
            'estatus' => ControlRenovacion::ESTATUS_VIGENTE,
            'fecha_recordatorio' => $fechaVencimiento->copy()->subDays(7),
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('renovaciones.index')
            ->with('success', 'Ciclo de renovación registrado correctamente.');
    }

    public function renovar(Request $request, ControlRenovacion $renovacion)
    {
        $validated = $request->validate([
            'meses' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $fechaVencimiento = $renovacion->fecha_vencimiento->copy()->addMonths((int) $validated['meses']);

        $renovacion->update([
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_vencimiento' => $fechaVencimiento,
            'estatus' => ControlRenovacion::ESTATUS_VIGENTE,
            'fecha_recordatorio' => $fechaVencimiento->copy()->subDays(7),
        ]);

        $cliente = $renovacion->cliente;
        if ($cliente->user->estatus === User::ESTATUS_SUSPENDIDO) {
            $cliente->user->update(['estatus' => User::ESTATUS_ACTIVO]);
        }

        return redirect()->route('renovaciones.index')
            ->with('success', 'Renovación registrada correctamente. Próximo vencimiento: ' . $renovacion->fecha_vencimiento->format('d/m/Y'));
    }
}
