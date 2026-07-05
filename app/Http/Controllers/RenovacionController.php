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
            ->whereDoesntHave('renovaciones', fn ($q) => $q->whereIn('estado', [ControlRenovacion::ESTADO_VIGENTE, ControlRenovacion::ESTADO_POR_VENCER]))
            ->get();

        return view('renovaciones.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha_inicio' => ['required', 'date'],
            'duracion_meses' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $fechaInicio = \Carbon\Carbon::parse($validated['fecha_inicio']);
        $duracionMeses = (int) $validated['duracion_meses'];

        ControlRenovacion::create([
            'cliente_id' => $validated['cliente_id'],
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaInicio->copy()->addMonths($duracionMeses),
            'duracion_meses' => $duracionMeses,
            'estado' => ControlRenovacion::ESTADO_VIGENTE,
        ]);

        return redirect()->route('renovaciones.index')
            ->with('success', 'Ciclo de renovación registrado correctamente.');
    }

    public function renovar(ControlRenovacion $renovacion)
    {
        $renovacion->update([
            'fecha_renovacion' => now(),
            'fecha_vencimiento' => $renovacion->fecha_vencimiento->copy()->addMonths($renovacion->duracion_meses),
            'estado' => ControlRenovacion::ESTADO_RENOVADO,
            'notificado_at' => null,
        ]);

        $cliente = $renovacion->cliente;
        if ($cliente->user->estatus === User::ESTATUS_SUSPENDIDO) {
            $cliente->user->update(['estatus' => User::ESTATUS_ACTIVO]);
        }

        return redirect()->route('renovaciones.index')
            ->with('success', 'Renovación registrada correctamente. Próximo vencimiento: ' . $renovacion->fecha_vencimiento->format('d/m/Y'));
    }
}
