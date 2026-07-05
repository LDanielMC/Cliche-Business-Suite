<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaqueteAprobacionController extends Controller
{
    public function index()
    {
        $paquetes = PaqueteAprobacion::with('cliente.user')->withCount('fotos')
            ->latest('fecha_limite')
            ->paginate(10);

        return view('aprobaciones.index', compact('paquetes'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('aprobaciones.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'mes_revision' => ['required', 'date_format:Y-m'],
            'fecha_limite' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $cliente = Cliente::findOrFail($validated['cliente_id']);

        $paquete = PaqueteAprobacion::create($validated + [
            'fecha_envio' => now()->format('Y-m-d'),
            'cantidad_requerida' => $cliente->cantidad_fotos,
            'estatus' => PaqueteAprobacion::ESTATUS_PENDIENTE,
        ]);

        // Las fotos que el cliente decidió conservar en un ciclo anterior
        // pasan a ser candidatas de este nuevo paquete.
        $paqueteAnterior = PaqueteAprobacion::where('cliente_id', $cliente->id)
            ->where('id', '!=', $paquete->id)
            ->latest('fecha_limite')
            ->first();

        if ($paqueteAnterior) {
            FotoAprobacion::where('paquete_aprobacion_id', $paqueteAnterior->id)
                ->where('estatus', FotoAprobacion::ESTATUS_CONSERVADA)
                ->update([
                    'paquete_aprobacion_id' => $paquete->id,
                    'estatus' => FotoAprobacion::ESTATUS_PENDIENTE,
                ]);
        }

        return redirect()->route('aprobaciones.show', $paquete)
            ->with('success', 'Paquete de aprobación creado. Ahora sube las fotografías candidatas.');
    }

    public function show(PaqueteAprobacion $paquete)
    {
        $paquete->load(['cliente.user', 'fotos']);

        return view('aprobaciones.show', ['paquete' => $paquete]);
    }

    public function uploadFotos(Request $request, PaqueteAprobacion $paquete)
    {
        $validated = $request->validate([
            'fotos' => ['required', 'array', 'min:1'],
            'fotos.*' => ['image', 'max:5120'],
        ]);

        foreach ($validated['fotos'] as $archivo) {
            $ruta = $archivo->store("fotos-aprobacion/{$paquete->cliente_id}/{$paquete->id}", 'public');

            FotoAprobacion::create([
                'paquete_aprobacion_id' => $paquete->id,
                'ruta_foto' => $ruta,
                'estatus' => FotoAprobacion::ESTATUS_PENDIENTE,
            ]);
        }

        return redirect()->route('aprobaciones.show', $paquete)
            ->with('success', 'Fotografías cargadas correctamente.');
    }

    public function destroyFoto(PaqueteAprobacion $paquete, FotoAprobacion $foto)
    {
        if ($foto->estatus === FotoAprobacion::ESTATUS_APROBADA) {
            return redirect()->route('aprobaciones.show', $paquete)
                ->with('error', 'No se puede eliminar una fotografía ya aprobada.');
        }

        Storage::disk('public')->delete($foto->ruta_foto);
        $foto->delete();

        return redirect()->route('aprobaciones.show', $paquete)
            ->with('success', 'Fotografía eliminada.');
    }

    public function destroy(PaqueteAprobacion $paquete)
    {
        foreach ($paquete->fotos as $foto) {
            Storage::disk('public')->delete($foto->ruta_foto);
        }

        $paquete->delete();

        return redirect()->route('aprobaciones.index')
            ->with('success', 'Paquete de aprobación eliminado.');
    }

    public function misAprobaciones()
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();

        $paquetes = PaqueteAprobacion::where('cliente_id', $cliente->id)
            ->withCount('fotos')
            ->latest('fecha_limite')
            ->paginate(10);

        return view('aprobaciones.mis-aprobaciones', compact('paquetes'));
    }

    public function showCliente(PaqueteAprobacion $paquete)
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();
        abort_unless($paquete->cliente_id === $cliente->id, 403);

        $paquete->load('fotos');

        return view('aprobaciones.seleccionar', ['paquete' => $paquete]);
    }

    public function confirmarSeleccion(Request $request, PaqueteAprobacion $paquete)
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();
        abort_unless($paquete->cliente_id === $cliente->id, 403);

        if ($paquete->estatus !== PaqueteAprobacion::ESTATUS_PENDIENTE) {
            return redirect()->route('cliente.aprobaciones.index')
                ->with('error', 'Este paquete ya fue procesado.');
        }

        $idsValidos = $paquete->fotos()->pluck('id')->all();

        $validated = $request->validate([
            'aprobadas' => ['nullable', 'array', 'max:' . $paquete->cantidad_requerida],
            'aprobadas.*' => ['exists:fotos_aprobacion,id'],
            'conservar' => ['nullable', 'array'],
            'conservar.*' => ['exists:fotos_aprobacion,id'],
        ]);

        $aprobadas = array_intersect($validated['aprobadas'] ?? [], $idsValidos);
        $conservar = array_intersect($validated['conservar'] ?? [], $idsValidos);
        $conservar = array_diff($conservar, $aprobadas);

        if (empty($aprobadas)) {
            return redirect()->route('cliente.aprobaciones.show', $paquete)
                ->with('error', 'Selecciona al menos una fotografía para este ciclo.');
        }

        FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
            ->whereIn('id', $aprobadas)
            ->update(['estatus' => FotoAprobacion::ESTATUS_APROBADA]);

        FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
            ->whereIn('id', $conservar)
            ->update(['estatus' => FotoAprobacion::ESTATUS_CONSERVADA]);

        FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
            ->whereNotIn('id', array_merge($aprobadas, $conservar))
            ->update(['estatus' => FotoAprobacion::ESTATUS_DESCARTADA]);

        $paquete->update(['estatus' => PaqueteAprobacion::ESTATUS_COMPLETADO]);

        $paquete->publicarEnCalendario(Auth::id());

        return redirect()->route('cliente.aprobaciones.index')
            ->with('success', 'Selección confirmada. Tus fotografías fueron enviadas al calendario de publicaciones.');
    }
}
