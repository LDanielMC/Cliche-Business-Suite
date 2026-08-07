<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PagoCliente;
use App\Support\Ordenable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoClienteController extends Controller
{
    use Ordenable;

    private const ORDEN_PAGOS = [
        'cliente' => 'clientes.nombre_negocio',
        'monto'   => 'pagos_clientes.monto',
        'fecha'   => 'pagos_clientes.fecha_pago',
    ];

    /**
     * Admin: payment history with filters.
     * Payments are generated automatically by the renovation flow — no manual creation.
     */
    public function index(Request $request)
    {
        $query = PagoCliente::with(['cliente', 'validador'])
            ->join('clientes', 'clientes.id', '=', 'pagos_clientes.cliente_id')
            ->select('pagos_clientes.*');

        if ($request->filled('cliente_id')) {
            $query->where('pagos_clientes.cliente_id', $request->input('cliente_id'));
        }

        if ($request->filled('forma_pago')) {
            $query->where('pagos_clientes.forma_pago', $request->input('forma_pago'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('pagos_clientes.fecha_pago', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('pagos_clientes.fecha_pago', '<=', $request->input('hasta'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('clientes.nombre_negocio', 'like', "%{$q}%")
                    ->orWhere('pagos_clientes.concepto_servicio', 'like', "%{$q}%");
            });
        }

        $this->aplicarOrden($query, self::ORDEN_PAGOS, 'pagos_clientes.fecha_pago', 'desc');

        $pagos    = $query->paginate(15)->withQueryString();
        $clientes = Cliente::with('user')->whereHas('user')->orderBy('id')->get();

        return view('pagos.index', compact('pagos', 'clientes'));
    }

    /**
     * Client: view their own payment history.
     */
    public function misPagos()
    {
        $cliente = Cliente::where('user_id', Auth::id())->firstOrFail();
        $pagos   = PagoCliente::where('cliente_id', $cliente->id)
                              ->latest('fecha_pago')
                              ->paginate(10);

        return view('pagos.mis-pagos', compact('pagos'));
    }
}
