<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h2 style="color:#b91c1c;">⚠ Auto-aprobación incompleta</h2>

    <p>
        El sistema procesó el paquete <strong>#{{ $paquete->id }}</strong> del cliente
        <strong>{{ $paquete->cliente->nombre_negocio }}</strong>
        ({{ $paquete->mes_revision_legible }}).
    </p>

    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Fotografías requeridas</td>
            <td style="padding:8px;border:1px solid #ddd;">{{ $requeridas }}</td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Fotografías aprobadas</td>
            <td style="padding:8px;border:1px solid #ddd;">{{ $aprobadas }}</td>
        </tr>
        <tr style="background:#fef2f2;">
            <td style="padding:8px;border:1px solid #ddd;font-weight:bold;">Faltantes</td>
            <td style="padding:8px;border:1px solid #ddd;color:#b91c1c;">{{ $requeridas - $aprobadas }}</td>
        </tr>
    </table>

    <p>
        No había suficientes fotografías disponibles para completar la selección.
        Se aprobaron todas las disponibles. Revisa el paquete y agrega más fotografías
        si es necesario para el siguiente ciclo.
    </p>

    <p style="color:#666;font-size:13px;">
        Las fotografías sobrantes fueron enviadas al Banco de Reserva según la configuración del sistema.
    </p>

    <hr style="border:none;border-top:1px solid #eee;margin:32px 0;">
    <p style="color:#999;font-size:12px;">Cliche Business Suite — notificación automática</p>
</body>
</html>
