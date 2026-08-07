<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 60px 50px 70px 50px; }

        body { font-family: Helvetica, Arial, sans-serif; color: #1f2937; font-size: 13px; line-height: 1.5; }

        .encabezado { border-bottom: 3px solid #7c3aed; padding-bottom: 12px; margin-bottom: 20px; }
        h1 { font-size: 22px; color: #1f2937; margin: 0 0 6px 0; }
        .subtitle { color: #6b7280; font-size: 13px; margin: 0; }

        .resumen { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .resumen td { border: 1px solid #e5e7eb; padding: 14px; width: 33.33%; text-align: center; }
        .resumen .label { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 6px; }
        .resumen .valor { display: block; font-size: 19px; font-weight: bold; }
        .resumen .gastos .valor { color: #dc2626; }
        .resumen .categorias .valor { color: #2563eb; }
        .resumen .principal .valor { color: #b45309; font-size: 15px; }

        .grafica-caja { text-align: center; margin-bottom: 24px; padding: 16px 0; border: 1px solid #e5e7eb; }
        img.grafica { width: 520px; max-width: 100%; }

        h2.seccion { font-size: 15px; color: #1f2937; margin: 0 0 10px 0; }

        table.detalle { width: 100%; border-collapse: collapse; }
        table.detalle th { background: #f3f4f6; color: #374151; text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.03em; border-bottom: 2px solid #e5e7eb; }
        table.detalle td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        table.detalle tr.total td { border-top: 2px solid #374151; border-bottom: none; font-weight: bold; }

        .swatch { display: inline-block; width: 10px; height: 10px; margin-right: 8px; }

        .pie { color: #9ca3af; font-size: 10px; text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    @php
        $totalGastos = collect($filas)->sum('total');
        $categoriaPrincipal = collect($filas)->sortByDesc('total')->first();
        $colores = ['#7c3aed', '#14b8a6', '#f59e0b', '#dc3545', '#6366f1', '#0d9488', '#b45309', '#004085'];
    @endphp

    <div class="encabezado">
        <h1>Reporte de Gastos Operativos por Categoría</h1>
        <p class="subtitle">Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</p>
    </div>

    <table class="resumen">
        <tr>
            <td class="gastos">
                <span class="label">Gastos totales</span>
                <span class="valor">${{ number_format($totalGastos, 2) }}</span>
            </td>
            <td class="categorias">
                <span class="label">Categorías con gasto</span>
                <span class="valor">{{ count($filas) }}</span>
            </td>
            <td class="principal">
                <span class="label">Categoría principal</span>
                <span class="valor">{{ $categoriaPrincipal['categoria'] }} ({{ $categoriaPrincipal['porcentaje'] }}%)</span>
            </td>
        </tr>
    </table>

    <div class="grafica-caja">
        <img class="grafica" src="{{ $chartImage }}" alt="Gráfica de gastos por categoría">
    </div>

    <h2 class="seccion">Detalle por categoría</h2>
    <table class="detalle">
        <thead>
            <tr><th>Categoría</th><th>Total</th><th>Porcentaje</th></tr>
        </thead>
        <tbody>
            @foreach($filas as $i => $fila)
                <tr>
                    <td><span class="swatch" style="background: {{ $colores[$i % count($colores)] }};"></span>{{ $fila['categoria'] }}</td>
                    <td>${{ number_format($fila['total'], 2) }}</td>
                    <td>{{ $fila['porcentaje'] }}%</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                <td>${{ number_format($totalGastos, 2) }}</td>
                <td>100%</td>
            </tr>
        </tbody>
    </table>

    @isset($gastosDetalle)
        <h2 class="seccion" style="margin-top: 24px;">Desglose de gastos — {{ $filas[0]['categoria'] }}</h2>
        <table class="detalle">
            <thead>
                <tr><th>Fecha</th><th>Concepto</th><th>Cliente</th><th>Forma de pago</th><th>Monto</th></tr>
            </thead>
            <tbody>
                @foreach($gastosDetalle as $gasto)
                    <tr>
                        <td>{{ $gasto['fecha'] }}</td>
                        <td>{{ $gasto['concepto'] }}</td>
                        <td>{{ $gasto['cliente'] ?? '—' }}</td>
                        <td>{{ $gasto['forma_pago'] }}</td>
                        <td>${{ number_format($gasto['monto'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endisset

    <p class="pie">Generado el {{ now()->format('d/m/Y H:i') }} — Cliche Business Suite</p>
</body>
</html>
