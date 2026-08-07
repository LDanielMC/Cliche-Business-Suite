<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use App\Models\Cliente;
use App\Models\GastoOperativo;
use App\Models\User;
use App\Support\Ordenable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GastoOperativoController extends Controller
{
    use Ordenable;

    private const ORDEN_GASTOS = [
        'concepto'   => 'gastos_operativos.concepto_gasto',
        'monto'      => 'gastos_operativos.monto',
        'fecha'      => 'gastos_operativos.fecha_gasto',
        'registrado' => 'registradores.name',
    ];

    public function index(Request $request)
    {
        $query = GastoOperativo::with(['categoria', 'cliente', 'registradoPor'])
            ->leftJoin('users as registradores', 'registradores.id', '=', 'gastos_operativos.registrado_por')
            ->select('gastos_operativos.*');

        // El operador solo ve lo que él mismo registró; el admin ve todo.
        if (Auth::user()->role === User::ROLE_OPERADOR) {
            $query->where('gastos_operativos.registrado_por', Auth::id());
        }

        if ($request->filled('categoria_gasto_id')) {
            $query->where('gastos_operativos.categoria_gasto_id', $request->input('categoria_gasto_id'));
        }

        if ($request->filled('registrado_por')) {
            $query->where('gastos_operativos.registrado_por', $request->input('registrado_por'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('gastos_operativos.fecha_gasto', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('gastos_operativos.fecha_gasto', '<=', $request->input('hasta'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('gastos_operativos.concepto_gasto', 'like', "%{$q}%")
                    ->orWhere('gastos_operativos.observaciones', 'like', "%{$q}%")
                    ->orWhere('registradores.name', 'like', "%{$q}%")
                    ->orWhereHas('categoria', fn ($c) => $c->where('nombre', 'like', "%{$q}%"))
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre_negocio', 'like', "%{$q}%"));
            });
        }

        $this->aplicarOrden($query, self::ORDEN_GASTOS, 'gastos_operativos.fecha_gasto', 'desc');

        $gastos = $query->paginate(10)->withQueryString();
        $categorias = CategoriaGasto::orderBy('nombre')->get();

        $registradores = Auth::user()->isAdmin()
            ? User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERADOR])->orderBy('name')->get()
            : collect();

        return view('gastos.index', compact('gastos', 'categorias', 'registradores'));
    }

    public function create()
    {
        $categorias = CategoriaGasto::orderBy('nombre')->get();
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
        $this->autorizarPropietario($gasto);

        $categorias = CategoriaGasto::orderBy('nombre')->get();
        $clientes = Cliente::activos()->with('user')->get();

        return view('gastos.edit', compact('gasto', 'categorias', 'clientes'));
    }

    public function update(Request $request, GastoOperativo $gasto)
    {
        $this->autorizarPropietario($gasto);

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
        $this->autorizarPropietario($gasto);

        if ($gasto->comprobante) {
            Storage::disk('public')->delete($gasto->comprobante);
        }

        $gasto->delete();

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto operativo eliminado correctamente.');
    }

    /**
     * El admin puede editar/eliminar cualquier gasto; el operador solo los
     * que él mismo registró.
     */
    private function autorizarPropietario(GastoOperativo $gasto): void
    {
        $user = Auth::user();

        if ($user->role === User::ROLE_OPERADOR && $gasto->registrado_por !== $user->id) {
            abort(403, 'Solo puedes editar o eliminar los gastos que tú registraste.');
        }
    }
}
