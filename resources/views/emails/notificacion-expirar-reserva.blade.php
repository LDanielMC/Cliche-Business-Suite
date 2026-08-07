<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h2 style="color:#d97706;">⚠ Fotografías en reserva próximas a expirar</h2>

    <p>Las siguientes fotografías del Banco de Reserva expiran hoy y requieren una decisión:</p>

    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:8px;border:1px solid #ddd;text-align:left;">ID</th>
                <th style="padding:8px;border:1px solid #ddd;text-align:left;">Cliente</th>
                <th style="padding:8px;border:1px solid #ddd;text-align:left;">Expira</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fotos as $foto)
            <tr>
                <td style="padding:8px;border:1px solid #ddd;">#{{ $foto->id }}</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $foto->cliente->nombre_negocio }}</td>
                <td style="padding:8px;border:1px solid #ddd;">{{ $foto->fecha_expiracion_reserva->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        Ingresa al sistema para decidir si conservas o eliminas cada fotografía
        <strong>antes</strong> de que el sistema las elimine permanentemente.
    </p>

    <hr style="border:none;border-top:1px solid #eee;margin:32px 0;">
    <p style="color:#999;font-size:12px;">Cliche Business Suite — notificación automática del Banco de Reserva</p>
</body>
</html>
