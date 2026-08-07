<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use App\Support\Ordenable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaGastoController extends Controller
{
    use Ordenable;

    public function index(Request $request)
    {
        $query = CategoriaGasto::withCount('gastos');

        if ($request->filled('q')) {
            $query->where('nombre', 'like', '%' . $request->input('q') . '%');
        }

        $this->aplicarOrden($query, [
            'nombre' => 'nombre',
            'gastos' => 'gastos_count',
        ], 'nombre', 'asc');

        $categorias = $query->paginate(10)->withQueryString();

        return view('categorias-gastos.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias-gastos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias_gastos,nombre'],
        ]);

        CategoriaGasto::create($validated);

        return redirect()->route('categorias-gastos.index')
            ->with('success', 'Categoría de gasto creada correctamente.');
    }

    public function edit(CategoriaGasto $categoria)
    {
        $categoria->loadCount('gastos');

        return view('categorias-gastos.edit', compact('categoria'));
    }

    public function update(Request $request, CategoriaGasto $categoria)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('categorias_gastos', 'nombre')->ignore($categoria->id)],
        ]);

        $categoria->update($validated);

        return redirect()->route('categorias-gastos.index')
            ->with('success', 'Categoría de gasto actualizada correctamente.');
    }

    public function destroy(CategoriaGasto $categoria)
    {
        if ($categoria->gastos()->exists()) {
            return redirect()->route('categorias-gastos.index')
                ->with('error', 'No se puede eliminar: hay gastos registrados con esta categoría.');
        }

        $categoria->delete();

        return redirect()->route('categorias-gastos.index')
            ->with('success', 'Categoría de gasto eliminada correctamente.');
    }
}
