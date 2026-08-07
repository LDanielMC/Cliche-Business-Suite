<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivation;
use App\Models\User;
use App\Support\Ordenable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OperadorController extends Controller
{
    use Ordenable;

    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_OPERADOR);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%");
            });
        }

        $this->aplicarOrden($query, [
            'operador' => 'name',
            'telefono' => 'telefono',
            'estatus'  => 'estatus',
        ], 'created_at', 'desc');

        $operadores = $query->paginate(10)->withQueryString();

        return view('operadores.index', compact('operadores'));
    }

    public function create()
    {
        return view('operadores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'digits:10'],
        ]);

        $token = bin2hex(random_bytes(32));

        $operador = User::create([
            'email' => $validated['email'],
            // Contraseña aleatoria imposible de adivinar; se sobreescribe al activar la cuenta
            'password' => Hash::make(Str::random(64)),
            'role' => User::ROLE_OPERADOR,
            'estatus' => User::ESTATUS_ACTIVO,
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'activation_token' => $token,
            'activation_expires_at' => now()->addHours(24),
        ]);

        Mail::to($operador->email)->send(new AccountActivation(route('activate.show', $token)));

        return redirect()->route('operadores.index')
            ->with('success', 'Operador registrado correctamente. Se envió un correo de activación a ' . $validated['email'] . ' (válido por 24 horas).');
    }

    public function show(User $operador)
    {
        $this->authorizeOperador($operador);

        return view('operadores.show', compact('operador'));
    }

    public function edit(User $operador)
    {
        $this->authorizeOperador($operador);

        return view('operadores.edit', compact('operador'));
    }

    public function update(Request $request, User $operador)
    {
        $this->authorizeOperador($operador);

        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->ignore($operador->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'digits:10'],
            'estatus' => ['required', Rule::in(User::ESTATUS)],
        ]);

        $data = [
            'email' => $validated['email'],
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'estatus' => $validated['estatus'],
            'fecha_baja' => $validated['estatus'] === User::ESTATUS_ACTIVO ? null : $operador->fecha_baja ?? now(),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $operador->update($data);

        return redirect()->route('operadores.index')
            ->with('success', 'Operador actualizado correctamente.');
    }

    public function destroy(User $operador)
    {
        $this->authorizeOperador($operador);

        $operador->update([
            'estatus' => User::ESTATUS_INACTIVO,
            'fecha_baja' => now(),
        ]);

        return redirect()->route('operadores.index')
            ->with('success', 'Operador dado de baja correctamente.');
    }

    private function authorizeOperador(User $operador): void
    {
        if ($operador->role !== User::ROLE_OPERADOR) {
            abort(404);
        }
    }
}
