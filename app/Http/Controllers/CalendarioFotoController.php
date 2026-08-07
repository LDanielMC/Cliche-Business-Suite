<?php

namespace App\Http\Controllers;

use App\Models\CalendarioFoto;
use App\Models\Cliente;
use App\Models\ControlRenovacion;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use App\Notifications\FotografiaCancelada;
use App\Notifications\FotografiaPublicada;
use App\Notifications\FotografiaReprogramada;
use App\Notifications\FotografiasProgramadas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CalendarioFotoController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Vistas
    // ══════════════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $mes  = (int) $request->input('mes',  now()->month);
        $anio = (int) $request->input('anio', now()->year);

        $publicaciones = CalendarioFoto::with(['cliente.user', 'fotoAprobacion'])
            ->whereYear('fecha_publicacion_programada',  $anio)
            ->whereMonth('fecha_publicacion_programada', $mes)
            ->whereIn('estatus', [
                CalendarioFoto::ESTATUS_PROGRAMADA,
                CalendarioFoto::ESTATUS_PUBLICADA,
                CalendarioFoto::ESTATUS_CANCELADA,
            ])
            ->orderBy('fecha_publicacion_programada')
            ->get()
            ->groupBy(fn ($item) => $item->fecha_publicacion_programada->format('Y-m-d'));

        $semanas = $this->construirGrid($mes, $anio);

        // ── Fotos aprobadas disponibles para programar ─────────────────────
        // "Disponible" = aprobada Y sin entrada activa en el calendario.
        // "Activa" = programada o publicada (excluye cancelada / pausada).
        // El match es por ID (foto_aprobacion_id), no por ruta.
        $idsScheduled = CalendarioFoto::whereIn('estatus', [
                CalendarioFoto::ESTATUS_PROGRAMADA,
                CalendarioFoto::ESTATUS_PUBLICADA,
            ])
            ->whereNotNull('foto_aprobacion_id')
            ->pluck('foto_aprobacion_id')
            ->all();

        $fotosDisponibles = FotoAprobacion::with(['paquete.cliente.user'])
            ->where('estatus', FotoAprobacion::ESTATUS_APROBADA)
            ->whereNotIn('id', $idsScheduled)
            ->get()
            ->groupBy(fn ($f) => $f->paquete->cliente_id);

        $clientes = Cliente::activos()->with('user')->get();

        return view('calendario.modern', compact(
            'publicaciones', 'semanas', 'mes', 'anio', 'clientes',
            'fotosDisponibles'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Colocación manual (FN.04)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Coloca una foto aprobada en un día del calendario.
     * Crea un CalendarioFoto en estado 'programada'.
     *
     * "Activa" = programada + publicada en todos los chequeos.
     *
     * Validaciones (en orden):
     *  1. Foto en estatus 'aprobada'.
     *  2. Foto sin entrada activa en el calendario (check por foto_aprobacion_id).
     *
     * Caso normal (paquete no vencido):
     *  3. Fecha dentro del snapshot del paquete (fecha_inicio_periodo … fecha_vencimiento_periodo),
     *     nunca antes de hoy (ver rangoValidoParaPaquete).
     *  4. No superar cantidad_requerida en el período. No hay tope por día:
     *     el admin decide libremente cuántas fotos caben en un mismo día
     *     (útil cuando el calendario se arma tarde y hay que ponerse al día).
     *  6. Renovación vigente o por_vencer.
     *
     * Caso foto "vencida" (su paquete.fecha_vencimiento_periodo ya pasó sin que se
     * agendara): es rezago de un ciclo ya pagado, no trabajo nuevo del ciclo actual.
     * Se permite ponerse al día sin las restricciones de arriba — sin tope por período,
     * sin tope por día, y aunque la renovación actual no esté vigente (el cliente puede
     * exigir que se publique lo pendiente antes de renovar). Solo se exige fecha >= hoy.
     *
     * En ambos casos:
     *  5. Cliente activo.
     *
     * Acepta una foto (foto_id) o varias a la vez (foto_ids[]), para poder
     * ponerse al día con varias fotos de rezago en un solo envío.
     *
     * Atomicidad: todo el lote va en un único DB::transaction con lock por
     * foto sobre FotoAprobacion. Cada foto se valida por separado — si una
     * falla (p. ej. ya estaba programada) no bloquea al resto del lote.
     * El unique (foto_activa_id) en BD provee la última línea de defensa
     * contra condiciones de carrera (una foto no puede quedar activa dos veces).
     */
    public function colocar(Request $request)
    {
        $validated = $request->validate([
            'foto_id'    => ['required_without:foto_ids', 'nullable', 'integer', 'exists:fotos_aprobacion,id'],
            'foto_ids'   => ['required_without:foto_id', 'nullable', 'array', 'min:1'],
            'foto_ids.*' => ['integer', 'exists:fotos_aprobacion,id'],
            'fecha'      => ['required', 'date'],
        ]);

        $fotoIds = !empty($validated['foto_ids']) ? $validated['foto_ids'] : [$validated['foto_id']];
        $fecha   = Carbon::parse($validated['fecha'])->startOfDay();

        return DB::transaction(function () use ($fotoIds, $fecha) {
            $programadas   = 0;
            $errores       = [];
            $nombreCliente = null;
            $porCliente    = []; // cliente_id => ['cliente' => Cliente, 'count' => n]

            foreach ($fotoIds as $fotoId) {
                $resultado = $this->colocarUnaFoto((int) $fotoId, $fecha);

                if ($resultado['ok']) {
                    $programadas++;
                    $nombreCliente = $resultado['cliente']->nombre_negocio;

                    $cid = $resultado['cliente']->id;
                    $porCliente[$cid] ??= ['cliente' => $resultado['cliente'], 'count' => 0];
                    $porCliente[$cid]['count']++;
                } else {
                    $errores[] = $resultado['error'];
                }
            }

            if ($programadas === 0) {
                return back()->with('error', implode(' ', $errores));
            }

            // Avisar a cada cliente afectado una sola vez, aunque se hayan
            // programado varias fotos suyas en el mismo envío.
            foreach ($porCliente as $item) {
                $item['cliente']->user->notify(new FotografiasProgramadas($item['count'], $fecha));
            }

            $mensaje = $programadas === 1
                ? 'Fotografía de ' . $nombreCliente . ' programada para el ' . $fecha->format('d/m/Y') . '.'
                : "{$programadas} fotografías de {$nombreCliente} programadas para el " . $fecha->format('d/m/Y') . '.';

            if (!empty($errores)) {
                $mensaje .= ' ' . count($errores) . ' no se pudo(ieron) programar: ' . implode(' ', $errores);
            }

            return back()->with('success', $mensaje);
        });
    }

    /**
     * Aplica todas las validaciones de colocar() a una sola foto y la crea
     * si pasa. Devuelve ['ok' => bool, 'cliente'|'error' => Cliente|string] en vez
     * de redirigir, para que colocar() pueda procesar un lote sin que una
     * foto inválida corte el resto.
     */
    private function colocarUnaFoto(int $fotoId, Carbon $fecha): array
    {
        // Bloquear la fila de FotoAprobacion para evitar dos colocaciones
        // concurrentes de la misma foto
        $foto = FotoAprobacion::lockForUpdate()
            ->with(['paquete.cliente.user', 'paquete.cliente.renovacionActiva'])
            ->find($fotoId);

        if (!$foto) {
            return ['ok' => false, 'error' => "Foto #{$fotoId} no existe."];
        }

        // ── 1. Foto aprobada ──────────────────────────────────────────
        if ($foto->estatus !== FotoAprobacion::ESTATUS_APROBADA) {
            return ['ok' => false, 'error' => "Foto #{$fotoId}: solo se pueden programar fotografías con estatus \"aprobada\"."];
        }

        // ── 2. Sin entrada activa (match por ID) ──────────────────────
        $yaScheduled = CalendarioFoto::where('foto_aprobacion_id', $foto->id)
            ->whereIn('estatus', [CalendarioFoto::ESTATUS_PROGRAMADA, CalendarioFoto::ESTATUS_PUBLICADA])
            ->exists();

        if ($yaScheduled) {
            return ['ok' => false, 'error' => "Foto #{$fotoId}: ya está programada en el calendario."];
        }

        $paquete = $foto->paquete;
        $cliente = $paquete->cliente;

        $esVencida = $paquete->fecha_vencimiento_periodo?->lt(Carbon::today()) ?? false;

        if ($esVencida) {
            // ── Rezago: solo se exige que no sea una fecha pasada ──────
            if ($fecha->lt(Carbon::today())) {
                return ['ok' => false, 'error' => "Foto #{$fotoId}: la fecha no puede ser anterior a hoy."];
            }
        } else {
            [$rangoInicio, $rangoFin] = $this->rangoValidoParaPaquete($paquete, $cliente);

            // ── 3. Fecha dentro del período válido ─────────────────────
            if ($rangoInicio->gt($rangoFin)) {
                return ['ok' => false, 'error' =>
                    'La vigencia de ' . $cliente->nombre_negocio .
                    ' ya venció (' . $rangoFin->format('d/m/Y') . ') y no quedan días disponibles ' .
                    'para programar en este ciclo. Espera a que se registre su renovación.'
                ];
            }

            if ($fecha->lt($rangoInicio) || $fecha->gt($rangoFin)) {
                return ['ok' => false, 'error' =>
                    "Foto #{$fotoId}: la fecha debe estar dentro del período vigente: " .
                    $rangoInicio->format('d/m/Y') . ' — ' . $rangoFin->format('d/m/Y') . '.'
                ];
            }

            // ── 4. No superar cantidad_requerida en el período ──────────
            // Sin tope por día: el admin reparte las fotos como necesite,
            // incluyendo varias el mismo día para ponerse al corriente.
            $programadasEnPeriodo = CalendarioFoto::where('cliente_id', $cliente->id)
                ->whereBetween('fecha_publicacion_programada', [
                    $rangoInicio->toDateString(),
                    $rangoFin->toDateString(),
                ])
                ->whereIn('estatus', [CalendarioFoto::ESTATUS_PROGRAMADA, CalendarioFoto::ESTATUS_PUBLICADA])
                ->count();

            if ($programadasEnPeriodo >= $paquete->cantidad_requerida) {
                return ['ok' => false, 'error' =>
                    "Ya hay {$programadasEnPeriodo} fotografía(s) programadas para el período de " .
                    $cliente->nombre_negocio . " (máximo: {$paquete->cantidad_requerida})."
                ];
            }
        }

        // ── 5. Cliente activo ─────────────────────────────────────────
        if ($cliente->user->estatus !== User::ESTATUS_ACTIVO) {
            return ['ok' => false, 'error' =>
                'No se puede programar una fotografía para ' . $cliente->nombre_negocio .
                ' porque su cuenta no está activa.'
            ];
        }

        // ── 6. Renovación vigente o por_vencer (no aplica a rezago) ────
        if (!$esVencida) {
            $renovacion       = $cliente->renovacionActiva;
            $estatusOperables = [ControlRenovacion::ESTATUS_VIGENTE, ControlRenovacion::ESTATUS_POR_VENCER];

            if (!$renovacion || !in_array($renovacion->estatus, $estatusOperables)) {
                return ['ok' => false, 'error' =>
                    'No se puede programar: ' . $cliente->nombre_negocio .
                    ' no tiene una renovación vigente o por vencer.'
                ];
            }
        }

        CalendarioFoto::create([
            'cliente_id'                   => $cliente->id,
            'foto_aprobacion_id'           => $foto->id,
            'fotografia_asociada'          => $foto->ruta_foto,
            'fecha_publicacion_programada' => $fecha->toDateString(),
            'estatus'                      => CalendarioFoto::ESTATUS_PROGRAMADA,
            'creado_por'                   => Auth::id(),
        ]);

        return ['ok' => true, 'cliente' => $cliente];
    }

    /**
     * Mueve una entrada programada a otra fecha.
     *
     * Repite las mismas validaciones de colocar() que dependen de la fecha:
     *  - Fecha dentro del snapshot del paquete (via FK → FotoAprobacion → paquete),
     *    nunca antes de hoy.
     *  - Cliente activo.
     *  - Renovación vigente o por_vencer.
     * Sin tope por día — mover no cambia el total del período.
     *
     * Bloquea entradas publicadas (irreversible) o no programadas.
     * Envuelto en DB::transaction con lock sobre la entrada para evitar
     * doble movimiento concurrente.
     */
    public function mover(Request $request, CalendarioFoto $calendario)
    {
        if ($calendario->estatus === CalendarioFoto::ESTATUS_PUBLICADA) {
            return back()->with('error', 'No se puede mover una fotografía ya publicada.');
        }

        if ($calendario->estatus !== CalendarioFoto::ESTATUS_PROGRAMADA) {
            return back()->with('error', 'Solo se pueden mover fotografías en estado "programada".');
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
        ]);

        $fecha = Carbon::parse($validated['fecha'])->startOfDay();

        return DB::transaction(function () use ($calendario, $fecha) {
            // Bloquear la entrada y re-verificar estatus dentro de la transacción
            $entrada = CalendarioFoto::lockForUpdate()->findOrFail($calendario->id);

            if ($entrada->estatus !== CalendarioFoto::ESTATUS_PROGRAMADA) {
                return back()->with('error', 'La fotografía ya no está en estado "programada".');
            }

            // Requerir el FK para poder validar el snapshot del paquete
            if (!$entrada->foto_aprobacion_id) {
                return back()->with('error',
                    'Esta entrada no tiene vínculo con una fotografía aprobada. ' .
                    'Elimínala y vuelve a colocar la foto desde el panel de disponibles.'
                );
            }

            $foto = FotoAprobacion::with(['paquete.cliente.user', 'paquete.cliente.renovacionActiva'])
                ->findOrFail($entrada->foto_aprobacion_id);

            $paquete = $foto->paquete;
            $cliente = $paquete->cliente;
            [$rangoInicio, $rangoFin] = $this->rangoValidoParaPaquete($paquete, $cliente);

            // ── Fecha dentro del período válido ────────────────────────────
            if ($rangoInicio->gt($rangoFin)) {
                return back()->with('error',
                    'La vigencia de ' . $cliente->nombre_negocio .
                    ' ya venció (' . $rangoFin->format('d/m/Y') . ') y no quedan días disponibles ' .
                    'para reprogramar en este ciclo. Espera a que se registre su renovación.'
                );
            }

            if ($fecha->lt($rangoInicio) || $fecha->gt($rangoFin)) {
                return back()->with('error',
                    'La nueva fecha debe estar dentro del período vigente: ' .
                    $rangoInicio->format('d/m/Y') . ' — ' .
                    $rangoFin->format('d/m/Y') . '.'
                );
            }

            // ── Cliente activo ─────────────────────────────────────────────
            if ($cliente->user->estatus !== User::ESTATUS_ACTIVO) {
                return back()->with('error',
                    'No se puede mover: ' . $cliente->nombre_negocio . ' no tiene cuenta activa.'
                );
            }

            // ── Renovación vigente o por_vencer ───────────────────────────
            $renovacion       = $cliente->renovacionActiva;
            $estatusOperables = [ControlRenovacion::ESTATUS_VIGENTE, ControlRenovacion::ESTATUS_POR_VENCER];

            if (!$renovacion || !in_array($renovacion->estatus, $estatusOperables)) {
                return back()->with('error',
                    'No se puede mover: ' . $cliente->nombre_negocio .
                    ' no tiene una renovación vigente o por vencer.'
                );
            }

            $fechaAnterior = $entrada->fecha_publicacion_programada->copy();

            $entrada->update(['fecha_publicacion_programada' => $fecha->toDateString()]);

            $cliente->user->notify(new FotografiaReprogramada($fechaAnterior, $fecha));

            return back()
                ->with('success', 'Fotografía reprogramada para el ' . $fecha->format('d/m/Y') . '.');
        });
    }

    /**
     * Publica manualmente una entrada programada (programada → publicada).
     * Las entradas publicadas no se pueden editar, mover ni eliminar.
     */
    public function publicar(CalendarioFoto $calendario)
    {
        if ($calendario->estatus !== CalendarioFoto::ESTATUS_PROGRAMADA) {
            return back()->with('error',
                'Solo se pueden publicar fotografías en estado "programada" ' .
                "(estatus actual: {$calendario->estatus})."
            );
        }

        $calendario->update(['estatus' => CalendarioFoto::ESTATUS_PUBLICADA]);

        $calendario->cliente->user->notify(new FotografiaPublicada($calendario->fecha_publicacion_programada));

        return back()
            ->with('success', 'Fotografía marcada como publicada.');
    }

    /**
     * Elimina una entrada del calendario.
     * Bloqueado para entradas publicadas.
     * No borra el archivo físico — la ruta es propiedad de fotos_aprobacion.
     */
    public function destroy(CalendarioFoto $calendario)
    {
        if ($calendario->estatus === CalendarioFoto::ESTATUS_PUBLICADA) {
            return back()->with('error', 'No se puede eliminar una fotografía ya publicada.');
        }

        // Solo avisar si el cliente llegó a verla en su calendario (el suyo
        // solo muestra programada/publicada — cancelada nunca fue visible).
        if ($calendario->estatus === CalendarioFoto::ESTATUS_PROGRAMADA) {
            $calendario->cliente->user->notify(new FotografiaCancelada($calendario->fecha_publicacion_programada));
        }

        $calendario->delete();

        return back()
            ->with('success', 'Entrada eliminada del calendario.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Formularios legados (upload directo de foto)
    // ══════════════════════════════════════════════════════════════════════════

    public function create()
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('calendario.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'                   => ['required', 'exists:clientes,id'],
            'fecha_publicacion_programada' => ['required', 'date'],
            'fotografia_asociada'          => ['required', 'image', 'max:5120'],
            'estatus'                      => ['required', 'in:' . implode(',', CalendarioFoto::ESTATUS)],
            'observaciones'                => ['nullable', 'string'],
        ]);

        $cliente = Cliente::with('user')->findOrFail($validated['cliente_id']);

        if ($cliente->user->estatus === User::ESTATUS_SUSPENDIDO) {
            return back()->withInput()
                ->with('error', 'No se puede programar una fotografía para ' .
                    $cliente->nombre_negocio . ' porque su cuenta está suspendida.');
        }

        $validated['fotografia_asociada'] = $request->file('fotografia_asociada')
            ->store('calendario-fotos/' . $validated['cliente_id'], 'public');

        CalendarioFoto::create($validated + ['creado_por' => Auth::id()]);

        $fecha = Carbon::parse($validated['fecha_publicacion_programada']);

        return redirect()->route('calendario.index', ['mes' => $fecha->month, 'anio' => $fecha->year])
            ->with('success', 'Publicación agregada al calendario correctamente.');
    }

    public function edit(CalendarioFoto $calendario)
    {
        if ($calendario->estatus === CalendarioFoto::ESTATUS_PUBLICADA) {
            return redirect()->route('calendario.index')
                ->with('error', 'No se puede editar una fotografía ya publicada.');
        }

        $clientes = Cliente::activos()->with('user')->get();

        return view('calendario.edit', ['publicacion' => $calendario, 'clientes' => $clientes]);
    }

    public function update(Request $request, CalendarioFoto $calendario)
    {
        if ($calendario->estatus === CalendarioFoto::ESTATUS_PUBLICADA) {
            return back()->with('error', 'No se puede editar una fotografía ya publicada.');
        }

        $validated = $request->validate([
            'cliente_id'                   => ['required', 'exists:clientes,id'],
            'fecha_publicacion_programada' => ['required', 'date'],
            'fotografia_asociada'          => ['nullable', 'image', 'max:5120'],
            'estatus'                      => ['required', 'in:' . implode(',', CalendarioFoto::ESTATUS)],
            'observaciones'                => ['nullable', 'string'],
        ]);

        if ($request->hasFile('fotografia_asociada')) {
            Storage::disk('public')->delete($calendario->fotografia_asociada);
            $validated['fotografia_asociada'] = $request->file('fotografia_asociada')
                ->store('calendario-fotos/' . $validated['cliente_id'], 'public');
        } else {
            unset($validated['fotografia_asociada']);
        }

        $calendario->update($validated);

        $fecha = Carbon::parse($validated['fecha_publicacion_programada']);

        return redirect()->route('calendario.index', ['mes' => $fecha->month, 'anio' => $fecha->year])
            ->with('success', 'Publicación actualizada correctamente.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CLIENTE — Calendario de solo lectura
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Muestra al cliente sus fotografías programadas y publicadas del período.
     * Solo lectura; no hay acciones disponibles.
     */
    public function clienteCalendario(Request $request)
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();

        $mes  = (int) $request->input('mes',  now()->month);
        $anio = (int) $request->input('anio', now()->year);

        $publicaciones = CalendarioFoto::with('fotoAprobacion')
            ->where('cliente_id', $cliente->id)
            ->whereIn('estatus', [CalendarioFoto::ESTATUS_PROGRAMADA, CalendarioFoto::ESTATUS_PUBLICADA])
            ->whereYear('fecha_publicacion_programada',  $anio)
            ->whereMonth('fecha_publicacion_programada', $mes)
            ->orderBy('fecha_publicacion_programada')
            ->get()
            ->groupBy(fn ($item) => $item->fecha_publicacion_programada->format('Y-m-d'));

        $semanas = $this->construirGrid($mes, $anio);

        return view('calendario.cliente', compact('publicaciones', 'semanas', 'mes', 'anio', 'cliente'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Calendario por cliente con historial de períodos
    // ══════════════════════════════════════════════════════════════════════════

    public function clienteView(Request $request, Cliente $cliente)
    {
        $cliente->load('user');

        // Todos los períodos de renovación del cliente, del más nuevo al más antiguo
        $periodos = ControlRenovacion::where('cliente_id', $cliente->id)
            ->orderByDesc('fecha_inicio')
            ->get();

        if ($periodos->isEmpty()) {
            return redirect()->route('calendario.index')
                ->with('error', "El cliente {$cliente->nombre_negocio} no tiene períodos de renovación registrados.");
        }

        // Período seleccionado (por defecto el más reciente)
        $periodoId     = $request->input('periodo_id');
        $periodoActual = $periodoId
            ? ($periodos->firstWhere('id', $periodoId) ?? $periodos->first())
            : $periodos->first();

        // Mes a mostrar (por defecto el mes de inicio del período seleccionado)
        $mes  = (int) $request->input('mes',  $periodoActual->fecha_inicio->month);
        $anio = (int) $request->input('anio', $periodoActual->fecha_inicio->year);

        // Entradas del calendario para este cliente en el mes solicitado
        $publicaciones = CalendarioFoto::with('fotoAprobacion')
            ->where('cliente_id', $cliente->id)
            ->whereYear('fecha_publicacion_programada', $anio)
            ->whereMonth('fecha_publicacion_programada', $mes)
            ->whereIn('estatus', [
                CalendarioFoto::ESTATUS_PROGRAMADA,
                CalendarioFoto::ESTATUS_PUBLICADA,
                CalendarioFoto::ESTATUS_CANCELADA,
            ])
            ->orderBy('fecha_publicacion_programada')
            ->get()
            ->groupBy(fn ($item) => $item->fecha_publicacion_programada->format('Y-m-d'));

        // Totales del período completo para el progress bar
        $totalesPeriodo = CalendarioFoto::where('cliente_id', $cliente->id)
            ->whereBetween('fecha_publicacion_programada', [
                $periodoActual->fecha_inicio->startOfDay(),
                $periodoActual->fecha_vencimiento->endOfDay(),
            ])
            ->whereIn('estatus', [CalendarioFoto::ESTATUS_PROGRAMADA, CalendarioFoto::ESTATUS_PUBLICADA])
            ->selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->pluck('total', 'estatus');

        // Conteo de fotos activas por período (para el sidebar)
        $conteosPorPeriodo = [];
        foreach ($periodos as $periodo) {
            $conteosPorPeriodo[$periodo->id] = CalendarioFoto::where('cliente_id', $cliente->id)
                ->whereBetween('fecha_publicacion_programada', [
                    $periodo->fecha_inicio->startOfDay(),
                    $periodo->fecha_vencimiento->endOfDay(),
                ])
                ->whereIn('estatus', [CalendarioFoto::ESTATUS_PROGRAMADA, CalendarioFoto::ESTATUS_PUBLICADA])
                ->count();
        }

        // Fotos aprobadas disponibles (sin entrada activa en el calendario)
        $idsScheduled = CalendarioFoto::where('cliente_id', $cliente->id)
            ->whereIn('estatus', [CalendarioFoto::ESTATUS_PROGRAMADA, CalendarioFoto::ESTATUS_PUBLICADA])
            ->whereNotNull('foto_aprobacion_id')
            ->pluck('foto_aprobacion_id')
            ->all();

        $fotosDisponibles = FotoAprobacion::with('paquete')
            ->whereHas('paquete', fn ($q) => $q->where('cliente_id', $cliente->id))
            ->where('estatus', FotoAprobacion::ESTATUS_APROBADA)
            ->whereNotIn('id', $idsScheduled)
            ->get();

        $semanas = $this->construirGrid($mes, $anio);

        return view('calendario.cliente-admin', compact(
            'cliente', 'periodos', 'periodoActual', 'mes', 'anio',
            'publicaciones', 'semanas', 'totalesPeriodo',
            'conteosPorPeriodo', 'fotosDisponibles'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPER PRIVADO
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Construye la grilla de semanas (arrays de 7 días, Lunes-Domingo)
     * para renderizar el calendario mensual como una tabla real.
     */
    private function construirGrid(int $mes, int $anio): array
    {
        $inicioMes = Carbon::create($anio, $mes, 1);
        $finMes    = $inicioMes->copy()->endOfMonth();

        $inicioGrid = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $finGrid    = $finMes->copy()->endOfWeek(Carbon::SUNDAY);

        $semanas = [];
        $cursor  = $inicioGrid->copy();

        while ($cursor->lte($finGrid)) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $semana[] = [
                    'fecha'  => $cursor->copy(),
                    'delMes' => $cursor->month === $mes,
                ];
                $cursor->addDay();
            }
            $semanas[] = $semana;
        }

        return $semanas;
    }

    /**
     * Rango de fechas válido para programar/mover una foto de este paquete.
     *
     * Caso normal (paquete abierto): el rango es el período congelado del
     * paquete — ya se mantiene sincronizado con la renovación activa vía
     * PaqueteAprobacion::resincronizarPeriodo().
     *
     * Caso paquete ya CERRADO (completado/auto_aprobado) cuyo período quedó
     * vencido antes de que el cliente renovara: ese snapshot nunca se vuelve
     * a tocar (para no reescribir historial), así que sus fotos aprobadas
     * quedarían bloqueadas para siempre. Si el cliente ya renovó a un ciclo
     * posterior, se usa el período de esa renovación vigente en su lugar.
     *
     * El calendario está pensado para automatizarse (publicación futura), así
     * que nunca se permite agendar en un día que ya pasó: el mínimo real del
     * rango es siempre hoy, aunque el período haya arrancado antes.
     *
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}
     */
    private function rangoValidoParaPaquete(PaqueteAprobacion $paquete, Cliente $cliente): array
    {
        $inicio = $paquete->fecha_inicio_periodo;
        $fin    = $paquete->fecha_vencimiento_periodo;

        $cerrado = in_array($paquete->estatus, [
            PaqueteAprobacion::ESTATUS_COMPLETADO,
            PaqueteAprobacion::ESTATUS_AUTO_APROBADO,
        ]);

        $renovacion = $cliente->renovacionActiva;

        if ($cerrado && $renovacion && $renovacion->fecha_inicio->gt($inicio)) {
            $inicio = $renovacion->fecha_inicio;
            $fin    = $renovacion->fecha_vencimiento;
        }

        $hoy = Carbon::today();
        if ($hoy->gt($inicio)) {
            $inicio = $hoy;
        }

        return [$inicio, $fin];
    }
}
