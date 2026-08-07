<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudRenovacion;
use App\Models\CalendarioFoto;
use App\Models\Cliente;
use App\Models\ClienteEstatusLog;
use App\Models\ControlRenovacion;
use App\Models\HistorialRenovacion;
use App\Models\PagoCliente;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use App\Notifications\ComprobantePagoRecibido;
use App\Notifications\PagoRechazado;
use App\Notifications\PagoValidado;
use App\Notifications\SolicitudRenovacionCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class RenovacionController extends Controller
{
    use \App\Support\Ordenable;

    private const ORDEN_RENOVACIONES = [
        'cliente'      => 'clientes.nombre_negocio',
        'vencimiento'  => 'control_renovaciones.fecha_vencimiento',
        'dias'         => 'control_renovaciones.fecha_vencimiento',
        'estatus'      => 'control_renovaciones.estatus',
    ];

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN VIEWS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Admin index: list all renewals filtered by status.
     */
    public function index(Request $request)
    {
        $filtro = $request->get('filtro', 'todas');

        $query = ControlRenovacion::with(['cliente.user', 'validador'])
            ->join('clientes', 'clientes.id', '=', 'control_renovaciones.cliente_id')
            ->select('control_renovaciones.*');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where('clientes.nombre_negocio', 'like', "%{$q}%");
        }

        if ($request->filled('sort')) {
            // Orden explícito del usuario reemplaza la prioridad por defecto.
            $this->aplicarOrden($query, self::ORDEN_RENOVACIONES, 'control_renovaciones.fecha_vencimiento', 'asc');
        } else {
            // CASE en vez de FIELD() para que funcione igual en MySQL y SQLite (tests).
            $query->orderByRaw("CASE control_renovaciones.estatus
                WHEN 'en_revision' THEN 1
                WHEN 'pago_rechazado' THEN 2
                WHEN 'factura_pendiente' THEN 3
                WHEN 'pago_validado' THEN 4
                WHEN 'por_vencer' THEN 5
                WHEN 'vigente' THEN 6
                WHEN 'vencido' THEN 7
                ELSE 8 END
            ")->orderBy('control_renovaciones.fecha_vencimiento');
        }

        if ($filtro === 'pendientes') {
            $query->whereIn('estatus', [
                ControlRenovacion::ESTATUS_EN_REVISION,
                ControlRenovacion::ESTATUS_FACTURA_PENDIENTE,
                ControlRenovacion::ESTATUS_PAGO_RECHAZADO,
            ]);
        } elseif ($filtro === 'por_vencer') {
            $query->where('estatus', ControlRenovacion::ESTATUS_POR_VENCER);
        } elseif ($filtro === 'vigentes') {
            $query->where('estatus', ControlRenovacion::ESTATUS_VIGENTE);
        } elseif ($filtro === 'vencidos') {
            $query->where('estatus', ControlRenovacion::ESTATUS_VENCIDO);
        } elseif ($filtro === 'solicitudes') {
            $query->where('solicitud_renovacion', true);
        }

        $renovaciones = $query->paginate(15)->withQueryString();

        $counters = ControlRenovacion::selectRaw('
            SUM(estatus IN ("en_revision","factura_pendiente","pago_rechazado")) as pendientes,
            SUM(estatus = "por_vencer") as por_vencer,
            SUM(estatus = "vigente") as vigentes,
            SUM(estatus = "vencido") as vencidos,
            SUM(solicitud_renovacion = 1) as solicitudes
        ')->first();

        return view('renovaciones.index', compact('renovaciones', 'filtro', 'counters'));
    }

    /**
     * Admin detail / review view for a single renewal.
     */
    public function show(ControlRenovacion $renovacion)
    {
        $renovacion->load(['cliente.user', 'validador', 'historial.validador']);

        return view('renovaciones.show', compact('renovacion'));
    }

    /**
     * Admin: create a new renewal cycle (initial onboarding only).
     */
    public function create()
    {
        $diasGracia = config('renovaciones.dias_gracia', 5);

        $clientes = Cliente::with('user')
            // Only active clients: new ones or manually reactivated after suspension
            ->whereHas('user', fn ($u) => $u->where('estatus', User::ESTATUS_ACTIVO))
            ->where(function ($q) use ($diasGracia) {
                // Case 1: Brand new — no renovation record at all
                $q->whereDoesntHave('renovaciones')
                  // Case 2: All renovations are vencido AND the latest vencimiento is
                  // past the grace period → the client went through vencido → suspendido
                  // and the admin manually reactivated them to activo
                  ->orWhere(function ($q) use ($diasGracia) {
                      $q->whereDoesntHave('renovaciones', fn ($r) => $r->whereNotIn('estatus', [
                              ControlRenovacion::ESTATUS_VENCIDO,
                          ]))
                        ->whereHas('renovaciones', fn ($r) => $r
                            ->where('estatus', ControlRenovacion::ESTATUS_VENCIDO)
                            ->whereDate('fecha_vencimiento', '<', now()->subDays($diasGracia)));
                  });
            })
            ->get();

        return view('renovaciones.create', compact('clientes'));
    }

    /**
     * Admin: pre-filled create form coming from a suspension renewal request.
     * Reuses the same create view with the client pre-selected and locked.
     */
    public function createDesdeSolicitud(ControlRenovacion $renovacion)
    {
        // Only makes sense when there is a pending request from a suspended client
        if (!$renovacion->tieneSolicitudPendiente()) {
            return redirect()->route('renovaciones.show', $renovacion)
                ->with('error', 'Esta renovación no tiene una solicitud pendiente.');
        }

        $clienteSeleccionado = $renovacion->cliente()->with('user')->first();

        return view('renovaciones.create', [
            'clientes'            => collect([$clienteSeleccionado]),
            'clienteSeleccionado' => $clienteSeleccionado,
            'renovacionSolicitud' => $renovacion,
        ]);
    }

    /**
     * Admin: store initial renewal cycle.
     *
     * The cycle starts in 'vencido' immediately so the client is required to
     * pay first before their 30-day service period begins. Grace days are
     * counted from today; if no payment arrives within that window the
     * VerificarRenovaciones cron will suspend the client automatically.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'    => ['required', 'exists:clientes,id'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $hoy        = now()->startOfDay();
        $diasGracia = config('renovaciones.dias_gracia', 5);

        $datos = [
            'fecha_inicio'         => $hoy,
            'fecha_vencimiento'    => $hoy->copy()->subDay(),
            'estatus'              => ControlRenovacion::ESTATUS_VENCIDO,
            'fecha_recordatorio'   => $hoy,
            'observaciones'        => $validated['observaciones'] ?? null,
            // Clear any leftover payment fields from the old cycle
            'comprobante_pago'     => null,
            'fecha_pago_cliente'   => null,
            'referencia_bancaria'  => null,
            'monto'                => null,
            'forma_pago'           => null,
            'solicita_factura'     => null,
            'motivo_rechazo'       => null,
            'rfc'                  => null,
            'razon_social'         => null,
            'codigo_postal_fiscal' => null,
            'regimen_fiscal'       => null,
            'uso_cfdi'             => null,
            'factura_pdf'          => null,
            'factura_xml'          => null,
            'validado_por'               => null,
            'fecha_validacion'           => null,
            // Clear pending request — the admin just approved it by creating the cycle
            'solicitud_renovacion'       => false,
            'fecha_solicitud_renovacion' => null,
        ];

        // Reuse the existing record if the client already has one (reactivation).
        // The system keeps exactly one control_renovaciones row per client; new
        // cycles are resets of that row, not additional rows.
        // Note: the client's User.estatus is intentionally left untouched here.
        // A suspended client stays suspended until a payment is submitted AND
        // validated (see completarRenovacion()) — there is no manual reactivation,
        // so traceability is guaranteed. The client is still allowed to submit a
        // payment proof while suspended (see CheckRole + puedeRenovar()).
        $existing = ControlRenovacion::where('cliente_id', $validated['cliente_id'])->first();

        if ($existing) {
            $existing->update($datos);
        } else {
            ControlRenovacion::create(array_merge($datos, ['cliente_id' => $validated['cliente_id']]));
        }

        return redirect()->route('renovaciones.index')
            ->with('success', "Ciclo registrado. El cliente tiene {$diasGracia} días para realizar su primer pago.");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN PAYMENT VALIDATION ACTIONS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Admin: validate the submitted payment proof.
     * If no invoice requested → auto-complete renewal.
     * If invoice requested → move to factura_pendiente.
     */
    public function validarPago(Request $request, ControlRenovacion $renovacion)
    {
        if ($renovacion->estatus !== ControlRenovacion::ESTATUS_EN_REVISION) {
            return back()->with('error', 'Esta renovación no está en revisión.');
        }

        DB::transaction(function () use ($renovacion) {
            if ($renovacion->solicita_factura) {
                $renovacion->update([
                    'estatus'          => ControlRenovacion::ESTATUS_FACTURA_PENDIENTE,
                    'validado_por'     => Auth::id(),
                    'fecha_validacion' => now(),
                ]);
            } else {
                $this->completarRenovacion($renovacion);
            }
        });

        $msg = $renovacion->solicita_factura
            ? 'Pago validado. Pendiente de cargar la factura.'
            : 'Pago validado y renovación completada exitosamente.';

        return redirect()->route('renovaciones.show', $renovacion)
            ->with('success', $msg);
    }

    /**
     * Admin: reject the payment proof and require resubmission.
     */
    public function rechazarPago(Request $request, ControlRenovacion $renovacion)
    {
        if ($renovacion->estatus !== ControlRenovacion::ESTATUS_EN_REVISION) {
            return back()->with('error', 'Esta renovación no está en revisión.');
        }

        $request->validate([
            'motivo_rechazo' => ['required', 'string', 'max:500'],
        ]);

        if ($renovacion->comprobante_pago) {
            Storage::disk('public')->delete($renovacion->comprobante_pago);
        }

        // Reset fecha_vencimiento to today so the cron starts a fresh grace
        // window from this rejection date — giving the client time to resubmit.
        // fecha_pago_cliente is intentionally kept: if the payment really happened
        // on that date, the next cycle will still be calculated from it correctly.
        $renovacion->update([
            'estatus'              => ControlRenovacion::ESTATUS_PAGO_RECHAZADO,
            'motivo_rechazo'       => $request->motivo_rechazo,
            'fecha_vencimiento'    => now()->startOfDay(),
            'comprobante_pago'     => null,
            // fecha_pago_cliente preserved intentionally
            'referencia_bancaria'  => null,
            'monto'                => null,
            'forma_pago'           => null,
            'solicita_factura'     => null,
            'rfc'                  => null,
            'razon_social'         => null,
            'codigo_postal_fiscal' => null,
            'regimen_fiscal'       => null,
            'uso_cfdi'             => null,
        ]);

        $renovacion->cliente->user->notify(new PagoRechazado($renovacion));

        return redirect()->route('renovaciones.show', $renovacion)
            ->with('success', 'Comprobante rechazado. El cliente deberá enviar uno nuevo.');
    }

    /**
     * Admin: upload PDF and XML invoice files.
     */
    public function cargarFactura(Request $request, ControlRenovacion $renovacion)
    {
        if ($renovacion->estatus !== ControlRenovacion::ESTATUS_FACTURA_PENDIENTE) {
            return back()->with('error', 'Esta renovación no requiere factura pendiente.');
        }

        $request->validate([
            'factura_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'factura_xml' => ['required', 'file', 'mimes:xml,text/xml', 'max:2048'],
        ]);

        $carpeta = "renovaciones/facturas/{$renovacion->cliente_id}";

        $pdfPath = $request->file('factura_pdf')->store($carpeta, 'public');
        $xmlPath = $request->file('factura_xml')->store($carpeta, 'public');

        DB::transaction(function () use ($renovacion, $pdfPath, $xmlPath) {
            $renovacion->update([
                'factura_pdf' => $pdfPath,
                'factura_xml' => $xmlPath,
            ]);

            $this->completarRenovacion($renovacion);
        });

        return redirect()->route('renovaciones.show', $renovacion)
            ->with('success', 'Factura cargada y renovación completada exitosamente.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CLIENT VIEWS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Client: view their current renewal status and history.
     */
    public function miRenovacion()
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $cliente = $user->cliente()->with([
            'renovacionActiva',
            'historialRenovaciones.validador',
        ])->firstOrFail();

        $renovacion = $cliente->renovacionActiva;

        // If the latest cycle is vencido with grace already expired AND the user is
        // activo (manually reactivated from dado_de_baja), they are in a limbo state:
        // the old cycle is dead and the admin hasn't created the new one yet.
        // Show "no active cycle" screen so they know to wait.
        //
        // Suspended clients are different: they are suspended precisely because
        // grace expired, so they must still see their vencido cycle to submit
        // a payment proof and get reactivated.
        if ($renovacion
            && $renovacion->estatus === ControlRenovacion::ESTATUS_VENCIDO
            && $renovacion->fecha_vencimiento->copy()->addDays(config('renovaciones.dias_gracia', 5))->lt(now())
            && $user->estatus === User::ESTATUS_ACTIVO
        ) {
            $renovacion = null;
        }

        return view('renovaciones.cliente.index', compact('cliente', 'renovacion'));
    }

    /**
     * Client: show the payment proof submission form.
     */
    public function formRenovar(ControlRenovacion $renovacion)
    {
        $this->autorizarCliente($renovacion);

        if (!$renovacion->puedeRenovar()) {
            return redirect()->route('renovaciones.cliente.index')
                ->with('error', 'No puedes enviar un comprobante en este momento.');
        }

        $cliente = $renovacion->cliente()->with('user')->first();

        return view('renovaciones.cliente.renovar', compact('renovacion', 'cliente'));
    }

    /**
     * Client: submit payment proof (and optional invoice data).
     */
    public function enviarComprobante(Request $request, ControlRenovacion $renovacion)
    {
        $this->autorizarCliente($renovacion);

        if (!$renovacion->puedeRenovar()) {
            return redirect()->route('renovaciones.cliente.index')
                ->with('error', 'No puedes enviar un comprobante en este momento.');
        }

        $request->validate([
            'comprobante_pago'     => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'fecha_pago_cliente'   => ['required', 'date', 'after_or_equal:' . $this->minFechaPago($renovacion), 'before_or_equal:today'],
            'monto'                => ['required', 'numeric', 'min:0.01'],
            'forma_pago'           => ['required', 'in:' . implode(',', PagoCliente::FORMA_PAGO)],
            'referencia_bancaria'  => ['nullable', 'string', 'max:100'],
            'solicita_factura'     => ['required', 'boolean'],
            // Fiscal data — only required when solicita_factura = 1
            'rfc'                  => ['nullable', 'required_if:solicita_factura,1', 'string', 'max:20'],
            'razon_social'         => ['nullable', 'required_if:solicita_factura,1', 'string', 'max:255'],
            'codigo_postal_fiscal' => ['nullable', 'required_if:solicita_factura,1', 'string', 'max:10'],
            'regimen_fiscal'       => ['nullable', 'required_if:solicita_factura,1', 'string', 'max:100'],
            'uso_cfdi'             => ['nullable', 'required_if:solicita_factura,1', 'string', 'max:10'],
        ]);

        $carpeta     = "renovaciones/comprobantes/{$renovacion->cliente_id}";
        $comprobante = $request->file('comprobante_pago')->store($carpeta, 'public');

        $solicita = (bool) $request->solicita_factura;
        $cliente  = $renovacion->cliente;

        // Persist fiscal data on the cliente record for future pre-fill
        if ($solicita) {
            $cliente->update([
                'rfc'                  => $request->rfc,
                'razon_social'         => $request->razon_social,
                'codigo_postal_fiscal' => $request->codigo_postal_fiscal,
                'regimen_fiscal'       => $request->regimen_fiscal,
                'uso_cfdi'             => $request->uso_cfdi,
            ]);
        }

        $renovacion->update([
            'estatus'              => ControlRenovacion::ESTATUS_EN_REVISION,
            'comprobante_pago'     => $comprobante,
            'fecha_pago_cliente'   => $request->fecha_pago_cliente,
            'referencia_bancaria'  => $request->referencia_bancaria,
            'monto'                => $request->monto,
            'forma_pago'           => $request->forma_pago,
            'solicita_factura'     => $solicita,
            'motivo_rechazo'       => null,
            // Fiscal fields
            'rfc'                  => $solicita ? $request->rfc : null,
            'razon_social'         => $solicita ? $request->razon_social : null,
            'codigo_postal_fiscal' => $solicita ? $request->codigo_postal_fiscal : null,
            'regimen_fiscal'       => $solicita ? $request->regimen_fiscal : null,
            'uso_cfdi'             => $solicita ? $request->uso_cfdi : null,
        ]);

        // Solo admin: Renovaciones no es un módulo que el operador pueda ver
        // ni gestionar, así que notificarle solo lo mandaría a un 403.
        $admins = User::where('role', User::ROLE_ADMIN)
            ->where('estatus', User::ESTATUS_ACTIVO)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new ComprobantePagoRecibido($renovacion->fresh()));
        }

        return redirect()->route('renovaciones.cliente.index')
            ->with('success', 'Comprobante enviado correctamente. El equipo lo revisará pronto.');
    }

    /**
     * Client (suspended): request a new renewal cycle from the admin.
     * No cycle is created here — the admin reviews and uses the existing
     * store() flow to create it. Prevents duplicate requests.
     */
    public function solicitarRenovacion(ControlRenovacion $renovacion)
    {
        $this->autorizarCliente($renovacion);

        // Guard: only suspended clients with an expired vencido cycle can request
        if (Auth::user()->estatus !== User::ESTATUS_SUSPENDIDO) {
            return redirect()->route('renovaciones.cliente.index')
                ->with('error', 'Solo los clientes suspendidos pueden solicitar una renovación.');
        }

        // Guard: no duplicate requests
        if ($renovacion->tieneSolicitudPendiente()) {
            return redirect()->route('renovaciones.cliente.index')
                ->with('error', 'Ya tienes una solicitud de renovación pendiente. El equipo te contactará pronto.');
        }

        // Guard: a cycle was already registered since the suspension — the client
        // should submit a payment proof directly instead of requesting again
        if ($renovacion->dentroDePeriodoGracia()) {
            return redirect()->route('renovaciones.cliente.index')
                ->with('error', 'Ya tienes un ciclo registrado. Envía tu comprobante de pago para reactivar tu cuenta.');
        }

        $renovacion->update([
            'solicitud_renovacion'       => true,
            'fecha_solicitud_renovacion' => now(),
        ]);

        // Notify all admins by email
        $admins = User::where('role', User::ROLE_ADMIN)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new SolicitudRenovacion($renovacion));
            $admin->notify(new SolicitudRenovacionCliente($renovacion));
        }

        return redirect()->route('renovaciones.cliente.index')
            ->with('success', 'Solicitud enviada correctamente. El equipo la revisará y te notificará cuando puedas realizar tu pago.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Complete a renewal cycle:
     *   1. Calculate the new service period (grace-aware; first-payment-aware).
     *   2. Archive current period in historial_renovaciones.
     *   3. Automatically create the corresponding PagoCliente record.
     *   4. Reset the control_renovaciones record for the new period.
     *   5. Re-sync any open PaqueteAprobacion to the new period.
     *   6. Reactivate client if suspended + log the event.
     */
    private function completarRenovacion(ControlRenovacion $renovacion): void
    {
        $diasGracia = config('renovaciones.dias_gracia', 5);
        $fechaPago  = $renovacion->fecha_pago_cliente; // Carbon date set by the client

        // Detect first payment BEFORE we create any historial record.
        $esPrimerPago = $renovacion->historial()->count() === 0;

        // 1. Calculate the new 30-day service period.
        $fueraDeGracia = false;

        if ($esPrimerPago) {
            // First payment: service starts on the exact payment date the client declared.
            $nuevaFechaInicio = $fechaPago->copy()->startOfDay();
        } else {
            // Regular renewal — grace-aware restart.
            $limiteGracia = $renovacion->fecha_vencimiento->copy()->addDays($diasGracia);

            if ($fechaPago->lte($limiteGracia)) {
                // Paid within grace: resume from the day after expiration (no gap charged).
                $nuevaFechaInicio = $renovacion->fecha_vencimiento->copy()->addDay();
            } else {
                // Paid late / suspended long time: fresh start on the payment date.
                $nuevaFechaInicio = $fechaPago->copy()->startOfDay();
                $fueraDeGracia   = true;
            }
        }

        $nuevaFechaVencimiento = $nuevaFechaInicio->copy()->addDays(29); // 30-day period

        // The PagoCliente covers the UPCOMING period when:
        //   a) it's the first payment (no previous period to cover), or
        //   b) the client paid way outside grace (e.g. suspended for months) — the old
        //      period is stale and the payment is really for the new cycle starting today.
        // Otherwise (paid within grace) it covers the period that just ended.
        $pagoParaPeriodoNuevo = $esPrimerPago || $fueraDeGracia;
        $periodoInicio = $pagoParaPeriodoNuevo ? $nuevaFechaInicio       : $renovacion->fecha_inicio;
        $periodoFin    = $pagoParaPeriodoNuevo ? $nuevaFechaVencimiento   : $renovacion->fecha_vencimiento;

        // 2. Archive current period
        $historial = HistorialRenovacion::create([
            'cliente_id'            => $renovacion->cliente_id,
            'control_renovacion_id' => $renovacion->id,
            'periodo_inicio'        => $periodoInicio,
            'periodo_fin'           => $periodoFin,
            'fecha_pago'            => $renovacion->fecha_pago_cliente,
            'monto'                 => $renovacion->monto,
            'referencia_bancaria'   => $renovacion->referencia_bancaria,
            'solicita_factura'      => (bool) $renovacion->solicita_factura,
            'comprobante_pago'      => $renovacion->comprobante_pago,
            'factura_pdf'           => $renovacion->factura_pdf,
            'factura_xml'           => $renovacion->factura_xml,
            'validado_por'          => $renovacion->validado_por ?? Auth::id(),
            'fecha_validacion'      => $renovacion->fecha_validacion ?? now(),
        ]);

        // 3. Generate the payment record automatically
        $cliente = $renovacion->cliente;
        PagoCliente::create([
            'cliente_id'              => $renovacion->cliente_id,
            'historial_renovacion_id' => $historial->id,
            'concepto_servicio'       => $cliente->servicio_contratado ?? 'Servicio mensual',
            'monto'                   => $renovacion->monto,
            'fecha_pago'              => $renovacion->fecha_pago_cliente,
            'periodo_inicio'          => $periodoInicio,
            'periodo_fin'             => $periodoFin,
            'forma_pago'              => $renovacion->forma_pago,
            'estatus'                 => PagoCliente::ESTATUS_PAGADO,
            'comprobante_pago'        => $renovacion->comprobante_pago,
            'factura_pdf'             => $renovacion->factura_pdf,
            'factura_xml'             => $renovacion->factura_xml,
            'validado_por'            => $renovacion->validado_por ?? Auth::id(),
            'fecha_validacion'        => $renovacion->fecha_validacion ?? now(),
            'registrado_por'          => Auth::id(),
        ]);

        // 4. Reset control record for the new period
        $renovacion->update([
            'fecha_inicio'         => $nuevaFechaInicio,
            'fecha_vencimiento'    => $nuevaFechaVencimiento,
            'fecha_recordatorio'   => $nuevaFechaVencimiento->copy()->subDays(5),
            'estatus'              => ControlRenovacion::ESTATUS_VIGENTE,
            // Clear all payment process fields
            'comprobante_pago'     => null,
            'fecha_pago_cliente'   => null,
            'referencia_bancaria'  => null,
            'monto'                => null,
            'forma_pago'           => null,
            'solicita_factura'     => null,
            'motivo_rechazo'       => null,
            'rfc'                  => null,
            'razon_social'         => null,
            'codigo_postal_fiscal' => null,
            'regimen_fiscal'       => null,
            'uso_cfdi'             => null,
            'factura_pdf'          => null,
            'factura_xml'          => null,
            'validado_por'         => null,
            'fecha_validacion'     => null,
        ]);

        // 5. Re-alinear el paquete de aprobación abierto (si lo hay) con el nuevo
        //    ciclo — evita que se quede apuntando a un período ya vencido.
        $paqueteAbierto = PaqueteAprobacion::where('cliente_id', $renovacion->cliente_id)
            ->whereIn('estatus', PaqueteAprobacion::ESTATUS_ACTIVOS)
            ->first();

        $paqueteAbierto?->resincronizarPeriodo($renovacion);

        // 6. Reactivate client if they were suspended (automatic — no manual override)
        if ($cliente->user->estatus === User::ESTATUS_SUSPENDIDO) {
            $cliente->user->update(['estatus' => User::ESTATUS_ACTIVO]);

            ClienteEstatusLog::create([
                'cliente_id'     => $cliente->id,
                'user_id'        => $cliente->user_id,
                'evento'         => ClienteEstatusLog::EVENTO_REACTIVACION,
                'fecha_evento'   => now()->toDateString(),
                'registrado_por' => Auth::id(),
                'observaciones'  => 'Reactivación automática por renovación completada.',
            ]);
        }

        $cliente->user->notify(new PagoValidado($renovacion->fresh()));
    }

    /**
     * Minimum allowed payment date for a given renovation.
     * Bounded to the tail end of the current cycle (vencimiento - dias_gracia) instead of
     * fecha_inicio, so a client already deep into their grace period can't backdate the
     * declared payment date into the middle of a period that's already fully elapsed.
     * Still never earlier than fecha_inicio (covers the first-payment case, where
     * fecha_inicio = today and fecha_vencimiento is a placeholder in the past).
     */
    private function minFechaPago(ControlRenovacion $renovacion): string
    {
        $diasGracia = config('renovaciones.dias_gracia', 5);
        $piso       = $renovacion->fecha_vencimiento->copy()->subDays($diasGracia)->min(now()->startOfDay());

        return $piso->max($renovacion->fecha_inicio->copy())->format('Y-m-d');
    }

    /**
     * Ensure the authenticated client owns this renovation record.
     */
    private function autorizarCliente(ControlRenovacion $renovacion): void
    {
        $cliente = Auth::user()->cliente;

        if (!$cliente || $renovacion->cliente_id !== $cliente->id) {
            abort(403);
        }
    }
}
