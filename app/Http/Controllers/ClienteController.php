<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivation;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('user')->activos()->latest()->paginate(10);
        return view('clientes.index', compact('clientes'));
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        $token = bin2hex(random_bytes(32));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => null,
            'role' => User::ROLE_CLIENTE,
            'activation_token' => $token,
            'activation_expires_at' => now()->addHours(24),
        ]);

        Cliente::create([
            'user_id' => $user->id,
            'nombre_negocio' => $validated['nombre_negocio'],
            'giro' => $validated['giro'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'servicio_contratado' => $validated['servicio_contratado'] ?? null,
            'cantidad_fotos' => $validated['cantidad_fotos'] ?? 0,
            'precio_mensual' => $validated['precio_mensual'] ?? 0,
        ]);

        $activationLink = route('activate.show', $token);
        Mail::to($user->email)->send(new AccountActivation($activationLink));

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($cliente->user_id)],
        ]);

        $cliente->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
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
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado lógicamente.');
    }

    public function eliminados()
    {
        $clientes = Cliente::with('user')->onlyTrashed()->latest()->paginate(10);
        return view('clientes.eliminados', compact('clientes'));
    }

    public function restaurar($id)
    {
        $cliente = Cliente::withTrashed()->findOrFail($id);
        $cliente->restore();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente restaurado correctamente.');
    }

    public function forceDestroy($id)
    {
        $cliente = Cliente::withTrashed()->findOrFail($id);
        $user = $cliente->user;

        $cliente->forceDelete();

        if ($user) {
            $user->delete();
        }

        return redirect()->route('clientes.eliminados')
            ->with('success', 'Cliente y usuario eliminados permanentemente.');
    }
}
