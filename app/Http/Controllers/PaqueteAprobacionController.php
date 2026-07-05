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
            'mes' => ['required', 'integer', 'between:1,12'],
            'anio' => ['required', 'integer', 'min:2020'],
            'fecha_limite' => ['required', 'date'],
        ]);

        $cliente = Cliente::findOrFail($validated['cliente_id']);

        $paquete = PaqueteAprobacion::create($validated + [
            'cantidad_requerida' => $cliente->cantidad_fotos,
            'estado' => PaqueteAprobacion::ESTADO_PENDIENTE,
        ]);

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

        $orden = $paquete->fotos()->max('orden') ?? 0;

        foreach ($validated['fotos'] as $archivo) {
            $orden++;
            $ruta = $archivo->store("fotos-aprobacion/{$paquete->cliente_id}/{$paquete->id}", 'public');

            FotoAprobacion::create([
                'paquete_aprobacion_id' => $paquete->id,
                'ruta_imagen' => $ruta,
                'estado' => FotoAprobacion::ESTADO_PENDIENTE,
                'orden' => $orden,
            ]);
        }

        return redirect()->route('aprobaciones.show', $paquete)
            ->with('success', 'Fotografías cargadas correctamente.');
    }

    public function destroyFoto(PaqueteAprobacion $paquete, FotoAprobacion $foto)
    {
        if ($foto->estado === FotoAprobacion::ESTADO_APROBADA) {
            return redirect()->route('aprobaciones.show', $paquete)
                ->with('error', 'No se puede eliminar una fotografía ya aprobada.');
        }

        Storage::disk('public')->delete($foto->ruta_imagen);
        $foto->delete();

        return redirect()->route('aprobaciones.show', $paquete)
            ->with('success', 'Fotografía eliminada.');
    }

    public function destroy(PaqueteAprobacion $paquete)
    {
        foreach ($paquete->fotos as $foto) {
            Storage::disk('public')->delete($foto->ruta_imagen);
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

        if ($paquete->estado !== PaqueteAprobacion::ESTADO_PENDIENTE) {
            return redirect()->route('cliente.aprobaciones.index')
                ->with('error', 'Este paquete ya fue procesado.');
        }

        $validated = $request->validate([
            'fotos_ids' => ['required', 'array', 'max:' . $paquete->cantidad_requerida],
            'fotos_ids.*' => ['exists:fotos_aprobacion,id'],
        ]);

        $idsValidos = $paquete->fotos()->pluck('id')->all();
        $seleccionadas = array_intersect($validated['fotos_ids'], $idsValidos);

        FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
            ->whereIn('id', $seleccionadas)
            ->update(['estado' => FotoAprobacion::ESTADO_APROBADA]);

        FotoAprobacion::where('paquete_aprobacion_id', $paquete->id)
            ->whereNotIn('id', $seleccionadas)
            ->update(['estado' => FotoAprobacion::ESTADO_DESCARTADA]);

        $paquete->update(['estado' => PaqueteAprobacion::ESTADO_COMPLETADO]);

        $paquete->publicarEnCalendario(Auth::id());

        return redirect()->route('cliente.aprobaciones.index')
            ->with('success', 'Selección confirmada. Tus fotografías fueron enviadas al calendario de publicaciones.');
    }
}
