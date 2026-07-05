<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PagoCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = PagoCliente::with('cliente');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->input('cliente_id'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_pago', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_pago', '<=', $request->input('hasta'));
        }

        $pagos = $query->latest('fecha_pago')->paginate(10)->withQueryString();
        $clientes = Cliente::activos()->with('user')->get();

        return view('pagos.index', compact('pagos', 'clientes'));
    }

    public function create()
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('pagos.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_pago' => ['required', 'date'],
            'metodo_pago' => ['nullable', 'string', 'max:50'],
            'concepto' => ['nullable', 'string', 'max:255'],
        ]);

        PagoCliente::create($validated + ['registrado_por' => Auth::id()]);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado correctamente.');
    }

    public function edit(PagoCliente $pago)
    {
        $clientes = Cliente::activos()->with('user')->get();

        return view('pagos.edit', compact('pago', 'clientes'));
    }

    public function update(Request $request, PagoCliente $pago)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_pago' => ['required', 'date'],
            'metodo_pago' => ['nullable', 'string', 'max:50'],
            'concepto' => ['nullable', 'string', 'max:255'],
        ]);

        $pago->update($validated);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy(PagoCliente $pago)
    {
        $pago->delete();

        return redirect()->route('pagos.index')
            ->with('success', 'Pago eliminado correctamente.');
    }

    public function misPagos()
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();
        $pagos = PagoCliente::where('cliente_id', $cliente->id)->latest('fecha_pago')->paginate(10);

        return view('pagos.mis-pagos', compact('pagos'));
    }
}
