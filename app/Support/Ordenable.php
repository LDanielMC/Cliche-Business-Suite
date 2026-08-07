<?php

namespace App\Support;

/**
 * Ordenamiento de tablas vía query string (?sort=&dir=), con columnas
 * permitidas en whitelist para no exponer nombres de columna arbitrarios.
 */
trait Ordenable
{
    protected function aplicarOrden($query, array $columnasPermitidas, string $porDefecto, string $direccionPorDefecto = 'asc')
    {
        $sort = request('sort');
        $dir  = request('dir') === 'desc' ? 'desc' : 'asc';

        $columna = array_key_exists($sort, $columnasPermitidas)
            ? $columnasPermitidas[$sort]
            : ($columnasPermitidas[$porDefecto] ?? $porDefecto);

        if (!$sort || !array_key_exists($sort, $columnasPermitidas)) {
            $dir = $direccionPorDefecto;
        }

        return $query->orderBy($columna, $dir);
    }
}
