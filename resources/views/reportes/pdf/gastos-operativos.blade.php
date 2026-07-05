<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 12px; }
        h1 { font-size: 18px; color: #667eea; margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #f8f9fa; }
        img.grafica { width: 300px; margin: 0 auto 10px auto; display: block; }
    </style>
</head>
<body>
    <h1>Reporte de Gastos Operativos por Categoría</h1>
    <div class="subtitle">Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</div>

    <img class="grafica" src="{{ $chartImage }}" alt="Gráfica de gastos por categoría">

    <table>
        <thead>
            <tr><th>Categoría</th><th>Total</th><th>Porcentaje</th></tr>
        </thead>
        <tbody>
            @foreach($filas as $fila)
                <tr>
                    <td>{{ $fila['categoria'] }}</td>
                    <td>${{ number_format($fila['total'], 2) }}</td>
                    <td>{{ $fila['porcentaje'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
