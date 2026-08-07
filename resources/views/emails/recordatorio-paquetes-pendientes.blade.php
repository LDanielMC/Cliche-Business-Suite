<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h2 style="color:#b91c1c;">⚠ Paquetes de aprobación sin enviar</h2>

    <p>
        Los siguientes clientes se acercan al fin de su período de renovación y todavía
        no tienen un paquete de fotografías enviado para su aprobación. Súbelas cuanto antes
        para que el cliente tenga tiempo suficiente de revisarlas.
    </p>

    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr style="background:#f3f4f6;">
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Cliente</td>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Vence el</td>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Días restantes</td>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Estado</td>
        </tr>
        @foreach($enRiesgo as $item)
            <tr style="{{ $item['nivel'] === 'critico' ? 'background:#fef2f2;' : '' }}">
                <td style="padding:8px;border:1px solid #ddd;">{{ $item['cliente']->nombre_negocio }}</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $item['cliente']->renovacionActiva->fecha_vencimiento->format('d/m/Y') }}</td>
                <td style="padding:8px;border:1px solid #ddd;color:{{ $item['nivel'] === 'critico' ? '#b91c1c' : '#92400e' }};font-weight:bold;">
                    {{ $item['etiqueta'] }}
                    {{ $item['nivel'] === 'critico' ? '— urgente' : '' }}
                </td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $item['paquete'] ? 'Borrador sin enviar' : 'Sin crear' }}</td>
            </tr>
        @endforeach
    </table>

    <p>
        <a href="{{ route('aprobaciones.index') }}" style="display:inline-block;padding:10px 18px;background:#5b4fd6;color:#fff;text-decoration:none;border-radius:6px;">
            Ver paquetes de aprobación
        </a>
    </p>

    <hr style="border:none;border-top:1px solid #eee;margin:32px 0;">
    <p style="color:#999;font-size:12px;">Cliche Business Suite — notificación automática</p>
</body>
</html>
