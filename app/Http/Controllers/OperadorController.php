<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OperadorController extends Controller
{
    public function index()
    {
        $operadores = User::where('role', User::ROLE_OPERADOR)
            ->latest()
            ->paginate(10);

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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'estatus' => ['required', Rule::in(User::ESTATUS)],
        ]);

        User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_OPERADOR,
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'estatus' => $validated['estatus'] ?? User::ESTATUS_ACTIVO,
        ]);

        return redirect()->route('operadores.index')
            ->with('success', 'Operador registrado correctamente.');
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
            'telefono' => ['nullable', 'string', 'max:20'],
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
