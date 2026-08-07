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
        .totales td { font-weight: bold; }
        img.grafica { width: 100%; max-width: 650px; margin-top: 10px; }
        .resumen { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .resumen td { border: none; padding: 10px; width: 33.33%; text-align: center; border-radius: 6px; }
        .resumen .label { display: block; font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 4px; }
        .resumen .valor { display: block; font-size: 16px; font-weight: bold; }
        .resumen .ingresos { background: #ecfdf5; color: #059669; }
        .resumen .gastos { background: #fef2f2; color: #dc2626; }
        .resumen .utilidad { background: {{ $datos['utilidad'] >= 0 ? '#eff6ff' : '#fef2f2' }}; color: {{ $datos['utilidad'] >= 0 ? '#2563eb' : '#dc2626' }}; }
    </style>
</head>
<body>
    <h1>Reporte Financiero General</h1>
    <div class="subtitle">Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</div>

    <table class="resumen">
        <tr>
            <td class="ingresos">
                <span class="label">Ingresos</span>
                <span class="valor">${{ number_format($datos['total_ingresos'], 2) }}</span>
            </td>
            <td class="gastos">
                <span class="label">Gastos</span>
                <span class="valor">${{ number_format($datos['total_gastos'], 2) }}</span>
            </td>
            <td class="utilidad">
                <span class="label">Utilidad</span>
                <span class="valor">${{ number_format($datos['utilidad'], 2) }}</span>
            </td>
        </tr>
    </table>

    <img class="grafica" src="{{ $chartImage }}" alt="Gráfica de ingresos vs gastos">

    <table>
        <thead>
            <tr><th>Periodo</th><th>Ingresos</th><th>Gastos</th></tr>
        </thead>
        <tbody>
            @foreach($datos['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>${{ number_format($datos['ingresos'][$i], 2) }}</td>
                    <td>${{ number_format($datos['gastos'][$i], 2) }}</td>
                </tr>
            @endforeach
            <tr class="totales">
                <td>Total</td>
                <td>${{ number_format($datos['total_ingresos'], 2) }}</td>
                <td>${{ number_format($datos['total_gastos'], 2) }}</td>
            </tr>
            <tr class="totales">
                <td colspan="2">Utilidad</td>
                <td>${{ number_format($datos['utilidad'], 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
