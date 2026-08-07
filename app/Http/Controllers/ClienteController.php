<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivation;
use App\Models\Cliente;
use App\Models\ClienteEstatusLog;
use App\Models\User;
use App\Notifications\ClienteNuevo;
use App\Support\Ordenable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    use Ordenable;

    private const ORDEN_CLIENTES = [
        'cliente'  => 'clientes.nombre_negocio',
        'servicio' => 'clientes.servicio_contratado',
        'precio'   => 'clientes.precio_mensual',
        'estado'   => 'users.estatus',
    ];

    public function index(Request $request)
    {
        $query = Cliente::with('user')->activos()
            ->join('users', 'users.id', '=', 'clientes.user_id')
            ->select('clientes.*');

        $this->buscarClientes($query, $request->input('q'));
        $this->aplicarOrden($query, self::ORDEN_CLIENTES, 'clientes.created_at', 'desc');

        $clientes = $query->paginate(10)->withQueryString();
        return view('clientes.index', compact('clientes'));
    }

    private function buscarClientes($query, ?string $q): void
    {
        if (!$q) {
            return;
        }

        $query->where(function ($sub) use ($q) {
            $sub->where('clientes.nombre_negocio', 'like', "%{$q}%")
                ->orWhere('clientes.servicio_contratado', 'like', "%{$q}%")
                ->orWhere('users.name', 'like', "%{$q}%")
                ->orWhere('users.email', 'like', "%{$q}%");
        });
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_negocio' => ['required', 'string', 'max:150'],
            'giro' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string'],
            'servicio_contratado' => ['nullable', 'string', 'max:150'],
            'cantidad_fotos' => ['nullable', 'integer', 'min:0'],
            'precio_mensual' => ['nullable', 'numeric', 'min:0'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $token = bin2hex(random_bytes(32));

        $cliente = DB::transaction(function () use ($validated, $token) {
            $user = User::create([
                'email' => $validated['email'],
                // Contraseña aleatoria imposible de adivinar; se sobreescribe al activar la cuenta
                'password' => Hash::make(Str::random(64)),
                'role' => User::ROLE_CLIENTE,
                'estatus' => User::ESTATUS_ACTIVO,
                'nombres' => $validated['nombres'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'] ?? null,
                'activation_token' => $token,
                'activation_expires_at' => now()->addHours(24),
            ]);

            $cliente = Cliente::create([
                'user_id' => $user->id,
                'nombre_negocio' => $validated['nombre_negocio'],
                'giro' => $validated['giro'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'servicio_contratado' => $validated['servicio_contratado'] ?? null,
                'cantidad_fotos' => $validated['cantidad_fotos'] ?? 0,
                'precio_mensual' => $validated['precio_mensual'] ?? 0,
                'fecha_registro' => now(),
            ]);

            ClienteEstatusLog::create([
                'cliente_id'    => $cliente->id,
                'user_id'       => $user->id,
                'evento'        => ClienteEstatusLog::EVENTO_ALTA,
                'fecha_evento'  => now()->toDateString(),
                'registrado_por' => Auth::id(),
            ]);

            Mail::to($user->email)->send(new AccountActivation(route('activate.show', $token)));

            return $cliente;
        });

        $this->notificarEquipoOperativo($cliente, reactivado: false);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado correctamente. Se envió un correo de activación a ' . $validated['email'] . ' (válido por 24 horas).');
    }

    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre_negocio' => ['required', 'string', 'max:150'],
            'giro' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string'],
            'servicio_contratado' => ['nullable', 'string', 'max:150'],
            'cantidad_fotos' => ['nullable', 'integer', 'min:0'],
            'precio_mensual' => ['nullable', 'numeric', 'min:0'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($cliente->user_id)],
        ]);

        $cliente->user->update([
            'email' => $validated['email'],
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
        ]);

        $cliente->update([
            'nombre_negocio' => $validated['nombre_negocio'],
            'giro' => $validated['giro'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'servicio_contratado' => $validated['servicio_contratado'] ?? null,
            'cantidad_fotos' => $validated['cantidad_fotos'] ?? 0,
            'precio_mensual' => $validated['precio_mensual'] ?? 0,
        ]);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->user->update([
            'estatus' => User::ESTATUS_DADO_DE_BAJA,
            'fecha_baja' => now(),
        ]);

        ClienteEstatusLog::create([
            'cliente_id'    => $cliente->id,
            'user_id'       => $cliente->user_id,
            'evento'        => ClienteEstatusLog::EVENTO_BAJA,
            'fecha_evento'  => now()->toDateString(),
            'registrado_por' => Auth::id(),
        ]);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente dado de baja correctamente.');
    }

    public function eliminados(Request $request)
    {
        $query = Cliente::with('user')->dadosDeBaja()
            ->join('users', 'users.id', '=', 'clientes.user_id')
            ->select('clientes.*');

        $this->buscarClientes($query, $request->input('q'));
        $this->aplicarOrden($query, [
            'cliente'    => 'clientes.nombre_negocio',
            'fecha_baja' => 'users.fecha_baja',
        ], 'users.fecha_baja', 'desc');

        $clientes = $query->paginate(10)->withQueryString();
        return view('clientes.eliminados', compact('clientes'));
    }

    public function suspendidos(Request $request)
    {
        $query = Cliente::with(['user', 'renovacionActiva'])->suspendidos()
            ->join('users', 'users.id', '=', 'clientes.user_id')
            ->select('clientes.*');

        $this->buscarClientes($query, $request->input('q'));
        $this->aplicarOrden($query, [
            'cliente' => 'clientes.nombre_negocio',
        ], 'clientes.nombre_negocio', 'asc');

        $clientes = $query->paginate(10)->withQueryString();
        return view('clientes.suspendidos', compact('clientes'));
    }

    /**
     * Reactivate a dado_de_baja client directly to activo.
     * The admin can then create a new renewal cycle for them.
     */
    public function restaurar($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->user->update([
            'estatus'    => User::ESTATUS_ACTIVO,
            'fecha_baja' => null,
        ]);

        ClienteEstatusLog::create([
            'cliente_id'     => $cliente->id,
            'user_id'        => $cliente->user_id,
            'evento'         => ClienteEstatusLog::EVENTO_RESTAURACION,
            'fecha_evento'   => now()->toDateString(),
            'registrado_por' => Auth::id(),
            'observaciones'  => 'Cliente reactivado por administrador.',
        ]);

        $this->notificarEquipoOperativo($cliente, reactivado: true);

        return redirect()->route('clientes.eliminados')
            ->with('success', 'Cliente reactivado correctamente.');
    }

    /**
     * Avisa a admins y operadores activos de un cliente nuevo/reactivado
     * que va a necesitar su primer (o próximo) paquete de fotos armado.
     */
    private function notificarEquipoOperativo(Cliente $cliente, bool $reactivado): void
    {
        $equipo = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERADOR])
            ->where('estatus', User::ESTATUS_ACTIVO)
            ->get();

        foreach ($equipo as $miembro) {
            $miembro->notify(new ClienteNuevo($cliente, $reactivado));
        }
    }

    public function perfil()
    {
        $cliente = Cliente::with('user')->where('user_id', Auth::id())->firstOrFail();

        return view('clientes.perfil', compact('cliente'));
    }
}
