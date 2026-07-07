<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use App\Models\Cliente;
use App\Models\GastoOperativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GastoOperativoController extends Controller
{
    public function index(Request $request)
    {
        $query = GastoOperativo::with(['categoria', 'cliente']);

        if ($request->filled('categoria_gasto_id')) {
            $query->where('categoria_gasto_id', $request->input('categoria_gasto_id'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_gasto', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_gasto', '<=', $request->input('hasta'));
        }

        $gastos = $query->latest('fecha_gasto')->paginate(10)->withQueryString();
        $categorias = CategoriaGasto::orderBy('nombre_categoria')->get();

        return view('gastos.index', compact('gastos', 'categorias'));
    }

    public function create()
    {
        $categorias = CategoriaGasto::orderBy('nombre_categoria')->get();
        $clientes = Cliente::activos()->with('user')->get();

        return view('gastos.create', compact('categorias', 'clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria_gasto_id' => ['required', 'exists:categorias_gastos,id'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'concepto_gasto' => ['required', 'string', 'max:200'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_gasto' => ['required', 'date'],
            'forma_pago' => ['required', 'in:' . implode(',', GastoOperativo::FORMA_PAGO)],
            'comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('comprobante')) {
            $validated['comprobante'] = $request->file('comprobante')->store('comprobantes-gastos', 'public');
        }

        GastoOperativo::create($validated + ['registrado_por' => Auth::id()]);

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto operativo registrado correctamente.');
    }

    public function edit(GastoOperativo $gasto)
    {
        $categorias = CategoriaGasto::orderBy('nombre_categoria')->get();
        $clientes = Cliente::activos()->with('user')->get();

        return view('gastos.edit', compact('gasto', 'categorias', 'clientes'));
    }

    public function update(Request $request, GastoOperativo $gasto)
    {
        $validated = $request->validate([
            'categoria_gasto_id' => ['required', 'exists:categorias_gastos,id'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'concepto_gasto' => ['required', 'string', 'max:200'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_gasto' => ['required', 'date'],
            'forma_pago' => ['required', 'in:' . implode(',', GastoOperativo::FORMA_PAGO)],
            'comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('comprobante')) {
            if ($gasto->comprobante) {
                Storage::disk('public')->delete($gasto->comprobante);
            }
            $validated['comprobante'] = $request->file('comprobante')->store('comprobantes-gastos', 'public');
        }

        $gasto->update($validated);

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto operativo actualizado correctamente.');
    }

    public function destroy(GastoOperativo $gasto)
    {
        if ($gasto->comprobante) {
            Storage::disk('public')->delete($gasto->comprobante);
        }

        $gasto->delete();

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto operativo eliminado correctamente.');
    }
}
