<?php

namespace App\Http\Controllers;

use App\Mail\PaqueteEnviadoCliente;
use App\Models\CalendarioFoto;
use App\Models\Cliente;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Models\User;
use App\Notifications\FotosDescartadasPorLimiteReserva;
use App\Notifications\PaqueteEnviado;
use App\Notifications\SeleccionFotosConfirmada;
use App\Support\Ordenable;
use App\Support\PaquetesEnRiesgo;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaqueteAprobacionController extends Controller
{
    use Ordenable;

    private const ORDEN_PAQUETES = [
        'cliente'  => 'clientes.nombre_negocio',
        'periodo'  => 'paquetes_aprobacion.mes_revision',
        'cuota'    => 'paquetes_aprobacion.cantidad_requerida',
        'fotos'    => 'fotos_count',
        'limite'   => 'paquetes_aprobacion.fecha_limite',
        'estado'   => 'paquetes_aprobacion.estatus',
    ];

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Listado y creación
    // ══════════════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        // select() antes de withCount(): select() reemplaza toda la cláusula
        // SELECT, así que si va después borraría la subconsulta de fotos_count.
        $query = PaqueteAprobacion::with('cliente.user')
            ->join('clientes', 'clientes.id', '=', 'paquetes_aprobacion.cliente_id')
            ->select('paquetes_aprobacion.*')
            ->withCount('fotos');

        if ($request->filled('q')) {
            $query->where('clientes.nombre_negocio', 'like', '%' . $request->input('q') . '%');
        }

        $this->aplicarOrden($query, self::ORDEN_PAQUETES, 'paquetes_aprobacion.created_at', 'desc');

        $paquetes = $query->paginate(10)->withQueryString();

        $enRiesgo = PaquetesEnRiesgo::detectar();

        return view('aprobaciones.index', compact('paquetes', 'enRiesgo'));
    }

    public function create()
    {
        $estatusOperables = [
            \App\Models\ControlRenovacion::ESTATUS_VIGENTE,
            \App\Models\ControlRenovacion::ESTATUS_POR_VENCER,
        ];

        $clientesConPaqueteActivo = PaqueteAprobacion::whereIn('estatus', PaqueteAprobacion::ESTATUS_ACTIVOS)
            ->pluck('cliente_id');

        // Solo clientes activos, con renovación vigente/por vencer, sin un
        // paquete ya abierto, y sin un paquete (de cualquier estatus) que ya
        // cubra el período actual — las mismas reglas que valida store().
        $clientes = Cliente::activos()
            ->with('user', 'renovacionActiva')
            ->whereNotIn('id', $clientesConPaqueteActivo)
            ->get()
            ->filter(fn ($cliente) => $cliente->renovacionActiva
                && in_array($cliente->renovacionActiva->estatus, $estatusOperables)
                && !$this->tienePaqueteEnPeriodoActual($cliente->id, $cliente->renovacionActiva))
            ->values();

        return view('aprobaciones.create', compact('clientes'));
    }

    /**
     * Crea el paquete en estado 'borrador'.
     * - Copia cantidad_requerida desde clientes.cantidad_fotos.
     * - Copia el snapshot del período vigente desde la renovación activa.
     * - Reingresa automáticamente las fotos del Banco de Reserva del cliente
     *   como candidatas (mismo efecto que reingresarFoto(), pero sin que el
     *   operador tenga que ir foto por foto) — no notifica al cliente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'    => ['required', 'exists:clientes,id'],
            'mes_revision'  => ['required', 'date_format:Y-m'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $cliente = Cliente::with('renovacionActiva')->findOrFail($validated['cliente_id']);

        if ($cliente->user->estatus !== User::ESTATUS_ACTIVO) {
            return back()->with('error', 'El cliente debe estar activo para crear un paquete de aprobación.');
        }

        // Guard aplicación (la BD también lo rechaza via columna generada)
        $activo = PaqueteAprobacion::where('cliente_id', $cliente->id)
            ->whereIn('estatus', PaqueteAprobacion::ESTATUS_ACTIVOS)
            ->exists();

        if ($activo) {
            return back()->with('error', 'El cliente ya tiene un paquete en borrador o en revisión. Ciérralo antes de crear uno nuevo.');
        }

        $renovacion = $cliente->renovacionActiva;
        if (!$renovacion) {
            return back()->with('error', 'El cliente no tiene un ciclo de renovación activo. Crea uno primero.');
        }

        // La especificación exige renovación vigente o por_vencer para operar.
        $estatusOperables = [
            \App\Models\ControlRenovacion::ESTATUS_VIGENTE,
            \App\Models\ControlRenovacion::ESTATUS_POR_VENCER,
        ];
        if (!in_array($renovacion->estatus, $estatusOperables)) {
            return back()->with('error',
                "No se puede crear un paquete: la renovación del cliente está en estado '{$renovacion->estatus}'. " .
                'Solo se permite con renovación vigente o por vencer.'
            );
        }

        // Un solo paquete por período de renovación, sin importar su estatus.
        // El guard de arriba ($activo) solo cubre borrador/pendiente; este
        // cubre el caso de que el período actual ya tenga uno completado o
        // auto-aprobado y el cliente todavía no haya renovado a un ciclo nuevo.
        if ($this->tienePaqueteEnPeriodoActual($cliente->id, $renovacion)) {
            return back()->with('error',
                'Este cliente ya tiene un paquete de aprobación para el período actual ' .
                '(' . $renovacion->fecha_inicio->format('d/m/Y') . ' — ' . $renovacion->fecha_vencimiento->format('d/m/Y') . ').'
            );
        }

        $paquete = PaqueteAprobacion::create([
            'cliente_id'                => $cliente->id,
            'mes_revision'              => $validated['mes_revision'],
            'cantidad_requerida'        => $cliente->cantidad_fotos,
            'estatus'                   => PaqueteAprobacion::ESTATUS_BORRADOR,
            'fecha_inicio_periodo'      => $renovacion->fecha_inicio,
            'fecha_vencimiento_periodo' => $renovacion->fecha_vencimiento,
            'observaciones'             => $validated['observaciones'] ?? null,
            // fecha_envio y fecha_limite quedan null hasta enviarACliente()
        ]);

        // Reingresa automáticamente las fotos del Banco de Reserva de este
        // cliente como candidatas del nuevo paquete (fecha_ingreso_reserva se
        // conserva a propósito: marca su origen para que, si el operador la
        // quita después, regrese a reserva en vez de borrarse para siempre).
        $fotosReserva = FotoAprobacion::where('estatus', FotoAprobacion::ESTATUS_CONSERVADA)
            ->where('cliente_id', $cliente->id)
            ->get();

        if ($fotosReserva->isNotEmpty()) {
            FotoAprobacion::whereIn('id', $fotosReserva->pluck('id'))->update([
                'paquete_aprobacion_id'    => $paquete->id,
                'estatus'                  => FotoAprobacion::ESTATUS_PENDIENTE,
                'prioridad'                => null,
                'fecha_expiracion_reserva' => null,
            ]);
        }

        $mensaje = 'Paquete creado en borrador. Sube las fotografías candidatas y asigna prioridades antes de enviarlo al cliente.';

        if ($fotosReserva->isNotEmpty()) {
            $mensaje = $fotosReserva->count() . ' fotografía(s) del Banco de Reserva se agregaron automáticamente como candidatas. ' . $mensaje;
        }

        return redirect()->route('aprobaciones.show', $paquete)->with('success', $mensaje);
    }

    /**
     * True si el cliente ya tiene un PaqueteAprobacion (de cualquier estatus)
     * cuyo período coincide con el de su renovación activa actual — es decir,
     * si el período de renovación vigente ya tiene un paquete asociado.
     */
    private function tienePaqueteEnPeriodoActual(int $clienteId, \App\Models\ControlRenovacion $renovacion): bool
    {
        return PaqueteAprobacion::where('cliente_id', $clienteId)
            ->whereDate('fecha_inicio_periodo', $renovacion->fecha_inicio)
            ->exists();
    }

    public function show(PaqueteAprobacion $paquete)
    {
        $paquete->load(['cliente.user', 'fotos']);

        return view('aprobaciones.show', ['paquete' => $paquete]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Gestión de fotos del paquete
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Sube fotografías candidatas. Solo disponible en fase 'borrador'.
     * Las imágenes se reescalan automáticamente a máx 2000px (lado mayor)
     * y se guardan como JPEG calidad 85 para reducir peso en disco.
     */
    public function uploadFotos(Request $request, PaqueteAprobacion $paquete)
    {
        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_BORRADOR) {
            return back()->with('error', 'Solo se pueden subir fotos mientras el paquete está en borrador.');
        }

        $request->validate([
            'fotos'   => ['required', 'array', 'min:1'],
            'fotos.*' => ['image', 'max:51200'],   // 50 MB por archivo (RAW de cámara)
        ]);

        $directorio = "fotos-aprobacion/{$paquete->cliente_id}/{$paquete->id}";
        $gcs        = $this->gcsClient();
        $bucket     = $gcs->bucket(config('filesystems.disks.gcs.bucket'));

        foreach ($request->file('fotos') as $archivo) {
            $nombreArchivo = uniqid('foto_', true) . '.jpg';
            $rutaGcs       = $directorio . '/' . $nombreArchivo;

            // Comprimir en temporal, subir a GCS con visibilidad pública
            $tmp = tempnam(sys_get_temp_dir(), 'cliche_') . '.jpg';
            $this->guardarImagenComprimida($archivo->getRealPath(), $tmp);

            $bucket->upload(fopen($tmp, 'r'), [
                'name'     => $rutaGcs,
                'metadata' => ['contentType' => 'image/jpeg'],
            ]);
            @unlink($tmp);

            FotoAprobacion::create([
                'paquete_aprobacion_id' => $paquete->id,
                'cliente_id'            => $paquete->cliente_id,
                'ruta_foto'             => $rutaGcs,
                'estatus'               => FotoAprobacion::ESTATUS_PENDIENTE,
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Fotografías cargadas correctamente.');
    }

    /**
     * Redimensiona y guarda como JPEG optimizado.
     * - Máximo 2000px en el lado mayor (mantiene proporción).
     * - Calidad 85 — invisible al ojo, ~80 % menos peso que RAW.
     */
    private function gcsClient(): StorageClient
    {
        return new StorageClient([
            'keyFilePath' => config('filesystems.disks.gcs.key_file'),
        ]);
    }

    private function guardarImagenComprimida(string $origen, string $destino, int $maxPx = 2000, int $calidad = 85): void
    {
        [$w, $h, $tipo] = getimagesize($origen);

        $src = match ($tipo) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($origen),
            IMAGETYPE_PNG  => imagecreatefrompng($origen),
            IMAGETYPE_WEBP => imagecreatefromwebp($origen),
            IMAGETYPE_GIF  => imagecreatefromgif($origen),
            default        => imagecreatefromjpeg($origen),
        };

        // Corregir orientación EXIF (fotos de móvil/cámara rotadas)
        if (function_exists('exif_read_data') && in_array($tipo, [IMAGETYPE_JPEG])) {
            $exif = @exif_read_data($origen);
            $orientacion = $exif['Orientation'] ?? 1;
            $src = match ($orientacion) {
                3 => imagerotate($src, 180, 0),
                6 => imagerotate($src, -90, 0),
                8 => imagerotate($src, 90, 0),
                default => $src,
            };
            // Actualizar dimensiones si se rotó
            if (in_array($orientacion, [6, 8])) {
                [$w, $h] = [$h, $w];
            }
        }

        // Calcular nuevas dimensiones sin superar $maxPx
        if ($w > $maxPx || $h > $maxPx) {
            if ($w >= $h) {
                $nw = $maxPx;
                $nh = (int) round($h * $maxPx / $w);
            } else {
                $nh = $maxPx;
                $nw = (int) round($w * $maxPx / $h);
            }
        } else {
            [$nw, $nh] = [$w, $h];
        }

        $dst = imagecreatetruecolor($nw, $nh);

        // Preservar transparencia para PNG
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparente = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparente);
        imagealphablending($dst, true);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        imagejpeg($dst, $destino, $calidad);

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Asigna o actualiza la prioridad de una foto. Solo en fase 'borrador'.
     * Valida unicidad con mensaje claro antes de que la BD lo rechace.
     */
    public function asignarPrioridad(Request $request, PaqueteAprobacion $paquete, FotoAprobacion $foto)
    {
        abort_unless($foto->paquete_aprobacion_id === $paquete->id, 404);

        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_BORRADOR) {
            return back()->with('error', 'Las prioridades solo se pueden cambiar mientras el paquete está en borrador.');
        }

        $request->validate([
            'prioridad' => ['required', 'integer', 'min:1'],
        ]);

        $nueva = (int) $request->prioridad;

        $duplicado = FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
            ->where('prioridad', $nueva)
            ->where('id', '!=', $foto->id)
            ->exists();

        if ($duplicado) {
            return back()->with('error', "La prioridad {$nueva} ya está asignada a otra foto de este paquete.");
        }

        $foto->update(['prioridad' => $nueva]);

        return back()->with('success', "Prioridad {$nueva} asignada correctamente.");
    }

    /**
     * Quita una foto candidata. Solo en fase 'borrador', no si ya está aprobada.
     *
     * Si la foto vino del Banco de Reserva (fecha_ingreso_reserva no nula —
     * marcada así por store() o reingresarFoto()), regresa a 'conservada' en
     * vez de borrarse: quitarla de este paquete no debe destruirla para
     * siempre. Solo las fotos subidas directas a este paquete se eliminan.
     */
    public function destroyFoto(PaqueteAprobacion $paquete, FotoAprobacion $foto)
    {
        abort_unless($foto->paquete_aprobacion_id === $paquete->id, 404);

        if ($foto->estatus === FotoAprobacion::ESTATUS_APROBADA) {
            return back()->with('error', 'No se puede eliminar una fotografía ya aprobada.');
        }

        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_BORRADOR) {
            return back()->with('error', 'No se pueden eliminar fotos una vez enviado el paquete al cliente.');
        }

        if ($foto->fecha_ingreso_reserva !== null) {
            $mesesReserva = config('renovaciones.meses_reserva', 6);

            // Se desprende del paquete (paquete_aprobacion_id => null) para no
            // depender de que este borrador siga existiendo — si se borra más
            // tarde, la cascada de BD ya no se la lleva con él.
            $foto->update([
                'paquete_aprobacion_id'    => null,
                'estatus'                  => FotoAprobacion::ESTATUS_CONSERVADA,
                'prioridad'                => null,
                'fecha_expiracion_reserva' => now()->addMonths($mesesReserva),
            ]);

            return back()->with('success', 'Fotografía regresada al Banco de Reserva (no se agregará a este paquete).');
        }

        Storage::disk('gcs')->delete($foto->ruta_foto);
        $foto->delete();

        return back()->with('success', 'Fotografía eliminada.');
    }

    /**
     * Elimina el paquete completo. Solo permitido en fase 'borrador'.
     *
     * Las fotos que vinieron del Banco de Reserva (fecha_ingreso_reserva no
     * nula) se desprenden del paquete y regresan a 'conservada' en vez de
     * borrarse — la FK paquete_aprobacion_id tiene cascadeOnDelete, así que
     * si no se desprenden antes de $paquete->delete(), la BD las borraría
     * junto con el paquete sin importar su estatus. Solo las fotos subidas
     * directas a este paquete (nunca fueron de reserva) se eliminan de verdad.
     */
    public function destroy(PaqueteAprobacion $paquete)
    {
        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_BORRADOR) {
            return back()->with('error', 'Solo se puede eliminar un paquete en borrador.');
        }

        $mesesReserva = config('renovaciones.meses_reserva', 6);

        foreach ($paquete->fotos as $foto) {
            if ($foto->fecha_ingreso_reserva !== null) {
                $foto->update([
                    'paquete_aprobacion_id'    => null,
                    'estatus'                  => FotoAprobacion::ESTATUS_CONSERVADA,
                    'prioridad'                => null,
                    'fecha_expiracion_reserva' => $foto->fecha_expiracion_reserva ?? now()->addMonths($mesesReserva),
                ]);
                continue;
            }

            Storage::disk('gcs')->delete($foto->ruta_foto);
        }

        $paquete->delete();

        return redirect()->route('aprobaciones.index')
            ->with('success', 'Paquete eliminado.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Banco de Reserva: reingreso a borrador
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Lista las fotos en reserva disponibles para este cliente que pueden
     * reingresarse al paquete en borrador.
     */
    public function reserva(PaqueteAprobacion $paquete)
    {
        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_BORRADOR) {
            return back()->with('error', 'Solo se puede reingresar fotos mientras el paquete está en borrador.');
        }

        $fotosReserva = FotoAprobacion::where('estatus', FotoAprobacion::ESTATUS_CONSERVADA)
            ->where('cliente_id', $paquete->cliente_id)
            ->where(function ($q) use ($paquete) {
                $q->whereNull('paquete_aprobacion_id')
                  ->orWhere('paquete_aprobacion_id', '!=', $paquete->id);
            })
            ->orderBy('fecha_expiracion_reserva')
            ->get();

        return view('aprobaciones.reserva', compact('paquete', 'fotosReserva'));
    }

    /**
     * Reingresa una foto del Banco de Reserva al paquete en borrador.
     * La foto debe ser del mismo cliente. Queda en 'pendiente' sin prioridad
     * para que el operador la asigne antes de enviar.
     */
    public function reingresarFoto(PaqueteAprobacion $paquete, FotoAprobacion $foto)
    {
        abort_unless($paquete->estatus === PaqueteAprobacion::ESTATUS_BORRADOR, 422,
            'El paquete destino debe estar en borrador.');

        abort_unless($foto->estatus === FotoAprobacion::ESTATUS_CONSERVADA, 422,
            'Solo se pueden reingresar fotos en estado "conservada".');

        abort_unless($foto->cliente_id === $paquete->cliente_id, 403,
            'No se puede reingresar una foto de otro cliente.');

        // fecha_ingreso_reserva se conserva a propósito: marca que esta foto
        // viene del Banco de Reserva, para que destroyFoto() la regrese ahí
        // en vez de borrarla si el operador la quita del paquete.
        $foto->update([
            'paquete_aprobacion_id'    => $paquete->id,
            'estatus'                  => FotoAprobacion::ESTATUS_PENDIENTE,
            'prioridad'                => null,
            'fecha_expiracion_reserva' => null,
        ]);

        return back()->with('success', 'Fotografía reingresada. Asígnale una prioridad antes de enviar el paquete.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN / OPERADOR — Envío al cliente
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Mueve el paquete de 'borrador' a 'pendiente' (En revisión):
     *  - Exige cliente activo y renovación pagada (vigente o por vencer): no se
     *    le mandan fotos a un cliente que no está al corriente o dado de baja.
     *  - Exige que todavía quede al menos un día dentro del período — evita
     *    depender solo del estatus, que el cron diario puede tardar en
     *    reflejar si la fecha de vencimiento ya pasó.
     *  - Calcula fecha_limite = now + dias_plazo_aprobacion, sin rebasar nunca
     *    fecha_vencimiento_periodo — el cliente debe poder revisar y aprobar
     *    dentro del mismo período de renovación que paga las fotos.
     *  - Notifica al cliente por Mailable.
     */
    public function enviarACliente(PaqueteAprobacion $paquete)
    {
        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_BORRADOR) {
            return back()->with('error', 'Solo se puede enviar un paquete que esté en borrador.');
        }

        $cliente = $paquete->cliente;

        if ($cliente->user->estatus !== User::ESTATUS_ACTIVO) {
            return back()->with('error',
                'No se puede enviar: la cuenta de ' . $cliente->nombre_negocio . ' no está activa.'
            );
        }

        $renovacion       = $cliente->renovacionActiva;
        $estatusOperables = [
            \App\Models\ControlRenovacion::ESTATUS_VIGENTE,
            \App\Models\ControlRenovacion::ESTATUS_POR_VENCER,
        ];

        if (!$renovacion || !in_array($renovacion->estatus, $estatusOperables)) {
            return back()->with('error',
                'No se puede enviar: la renovación del cliente no está vigente. ' .
                'Registra o valida su pago antes de mandarle las fotos.'
            );
        }

        if (now()->startOfDay()->gt($paquete->fecha_vencimiento_periodo)) {
            return back()->with('error',
                'No se puede enviar: el período de este paquete ya venció ' .
                '(' . $paquete->fecha_vencimiento_periodo->format('d/m/Y') . '). ' .
                'Espera a que se registre la renovación del cliente — el paquete se realineará solo.'
            );
        }

        $fotos = $paquete->fotos()->where('estatus', FotoAprobacion::ESTATUS_PENDIENTE)->get();

        if ($fotos->isEmpty()) {
            return back()->with('error', 'El paquete debe tener al menos una fotografía antes de enviarlo.');
        }

        // El cliente debe poder seleccionar exactamente cantidad_requerida fotos.
        // Si el paquete tiene menos, quedaría trabado sin poder confirmar.
        if ($fotos->count() < $paquete->cantidad_requerida) {
            return back()->with('error',
                "El paquete tiene {$fotos->count()} fotografía(s) pero el plan del cliente requiere " .
                "{$paquete->cantidad_requerida}. Agrega más fotos antes de enviarlo."
            );
        }

        $diasPlazo   = config('renovaciones.dias_plazo_aprobacion', 3);
        $fechaLimite = now()->addDays($diasPlazo)->min($paquete->fecha_vencimiento_periodo)->toDateString();

        $paquete->update([
            'estatus'      => PaqueteAprobacion::ESTATUS_PENDIENTE,
            'fecha_envio'  => now()->toDateString(),
            'fecha_limite' => $fechaLimite,
        ]);

        Mail::to($cliente->user->email)
            ->send(new PaqueteEnviadoCliente($paquete->fresh()));

        $cliente->user->notify(new PaqueteEnviado($paquete->fresh()));

        return redirect()->route('aprobaciones.show', $paquete)
            ->with('success', "Paquete enviado al cliente. Plazo: {$diasPlazo} días (hasta " .
                Carbon::parse($fechaLimite)->format('d/m/Y') . ').');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CLIENTE — Mis aprobaciones y selección exacta
    // ══════════════════════════════════════════════════════════════════════════

    public function misAprobaciones()
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();

        $paquetes = PaqueteAprobacion::where('cliente_id', $cliente->id)
            ->where('estatus', '!=', PaqueteAprobacion::ESTATUS_BORRADOR)
            ->withCount('fotos')
            ->latest()
            ->paginate(10);

        return view('aprobaciones.mis-aprobaciones', compact('paquetes'));
    }

    public function showCliente(PaqueteAprobacion $paquete)
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();
        abort_unless($paquete->cliente_id === $cliente->id, 403);
        abort_unless($paquete->estatus !== PaqueteAprobacion::ESTATUS_BORRADOR, 403);

        $paquete->load(['fotos' => fn ($q) => $q->orderBy('prioridad')]);

        return view('aprobaciones.seleccionar', ['paquete' => $paquete]);
    }

    /**
     * Confirma la selección final del cliente.
     *
     * Reglas de negocio (FN.08 §5):
     *  - Paquete en 'pendiente'.
     *  - count(aprobadas) === cantidad_requerida (exacto — ni más ni menos).
     *  - conservar[] → 'conservada' con fechas de vigencia (Banco de Reserva).
     *  - El resto → 'descartada'.
     *  - Cascade: si alguna foto aprobada tenía CalendarioFoto activo, pasa a 'cancelada'.
     *  - Paquete → 'completado' con motivo 'aprobada_por_cliente'.
     *  - No se crea ningún CalendarioFoto.
     */
    public function confirmarSeleccion(Request $request, PaqueteAprobacion $paquete)
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();
        abort_unless($paquete->cliente_id === $cliente->id, 403);

        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_PENDIENTE) {
            return redirect()->route('cliente.aprobaciones.index')
                ->with('error', 'Este paquete ya fue procesado.');
        }

        $idsDelPaquete = $paquete->fotos()->pluck('id')->all();

        $validated = $request->validate([
            'aprobadas'   => ['required', 'array'],
            'aprobadas.*' => ['integer', 'exists:fotos_aprobacion,id'],
            'conservar'   => ['nullable', 'array'],
            'conservar.*' => ['integer', 'exists:fotos_aprobacion,id'],
        ]);

        // Filtrar solo IDs del paquete (evitar IDOR)
        $aprobadas = array_values(array_intersect($validated['aprobadas'], $idsDelPaquete));
        $conservar = array_values(array_intersect($validated['conservar'] ?? [], $idsDelPaquete));
        $conservar = array_values(array_diff($conservar, $aprobadas));

        // ── Selección exacta: el backend rechaza si el conteo es distinto ─────
        if (count($aprobadas) !== (int) $paquete->cantidad_requerida) {
            return back()->with('error',
                "Debes seleccionar exactamente {$paquete->cantidad_requerida} fotografía(s) " .
                '(enviaste ' . count($aprobadas) . ').'
            );
        }

        $hoy             = Carbon::today();
        $mesesReserva    = config('renovaciones.meses_reserva', 6);
        $fechaIngreso    = $hoy->toDateString();
        $fechaExpiracion = $hoy->copy()->addMonths($mesesReserva)->toDateString();

        $descartadasPorLimite = collect();

        DB::transaction(function () use ($paquete, $aprobadas, $conservar, $fechaIngreso, $fechaExpiracion, &$descartadasPorLimite) {
            // ── Cascade: cancelar CalendarioFoto de fotos que dejan de estar aprobadas ──
            $sobrantes = array_values(array_diff($paquete->fotos()->pluck('id')->all(), $aprobadas));
            $this->cancelarCalendarioDeFotosAprobadas($paquete->cliente_id, $sobrantes);

            // ── Marcar aprobadas ──────────────────────────────────────────────
            FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
                ->whereIn('id', $aprobadas)
                ->update(['estatus' => FotoAprobacion::ESTATUS_APROBADA]);

            // ── Conservar en Banco de Reserva con vigencia (con límite de ciclos) ──
            if (!empty($conservar)) {
                $descartadasPorLimite = FotoAprobacion::conservarConLimite($conservar, $fechaIngreso, $fechaExpiracion);
            }

            // ── El resto → descartada ─────────────────────────────────────────
            FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
                ->whereNotIn('id', array_merge($aprobadas, $conservar))
                ->update(['estatus' => FotoAprobacion::ESTATUS_DESCARTADA]);

            // ── Cerrar paquete ────────────────────────────────────────────────
            $paquete->update([
                'estatus'             => PaqueteAprobacion::ESTATUS_COMPLETADO,
                'motivo_finalizacion' => PaqueteAprobacion::MOTIVO_APROBADO_CLIENTE,
            ]);
            // NOTE: No se crea ningún CalendarioFoto. La programación es manual en FN.04.
        });

        $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERADOR])
            ->where('estatus', User::ESTATUS_ACTIVO)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new SeleccionFotosConfirmada($paquete->fresh(), count($aprobadas)));

            if ($descartadasPorLimite->isNotEmpty()) {
                $admin->notify(new FotosDescartadasPorLimiteReserva($paquete->cliente, $descartadasPorLimite->count()));
            }
        }

        return redirect()->route('cliente.aprobaciones.index')
            ->with('success', 'Selección confirmada. El equipo programará tus fotografías en el calendario.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPER PRIVADO — Cascade al desaprobar
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Si alguna de las fotos estaba 'aprobada' y tiene una entrada activa en
     * el calendario, la marca como 'cancelada'.
     * Invariante: no puede quedar CalendarioFoto activa de una foto no aprobada.
     *
     * El match es por foto_aprobacion_id (FK), no por ruta, para evitar
     * que rutas compartidas cancelen la entrada equivocada.
     */
    private function cancelarCalendarioDeFotosAprobadas(int $clienteId, array $fotoIds): void
    {
        if (empty($fotoIds)) {
            return;
        }

        // Solo cancelar entradas de fotos que aún estén en 'aprobada'
        // (las que ya son descartada/conservada no tienen entrada activa)
        $aprobadaIds = FotoAprobacion::whereIn('id', $fotoIds)
            ->where('estatus', FotoAprobacion::ESTATUS_APROBADA)
            ->pluck('id')
            ->all();

        if (empty($aprobadaIds)) {
            return;
        }

        // Cancelar entradas activas (programada / pausada) vinculadas por FK.
        // Incluimos pausada para cubrir datos legacy; publicada no se puede cancelar.
        CalendarioFoto::where('cliente_id', $clienteId)
            ->whereIn('foto_aprobacion_id', $aprobadaIds)
            ->whereIn('estatus', [CalendarioFoto::ESTATUS_PROGRAMADA, CalendarioFoto::ESTATUS_PAUSADA])
            ->update(['estatus' => CalendarioFoto::ESTATUS_CANCELADA]);
    }
}
