<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <h2 style="color: #667eea;">Nueva solicitud de renovación</h2>
    <p>Se ha recibido una solicitud de renovación de servicio de un cliente suspendido.</p>

    <table style="width:100%; border-collapse:collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 8px 12px; background:#f3f4f6; font-weight:600; width:40%;">Cliente</td>
            <td style="padding: 8px 12px; border-bottom:1px solid #e5e7eb;">{{ $renovacion->cliente->nombre_negocio }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; background:#f3f4f6; font-weight:600;">Email</td>
            <td style="padding: 8px 12px; border-bottom:1px solid #e5e7eb;">{{ $renovacion->cliente->user->email }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; background:#f3f4f6; font-weight:600;">Fecha de solicitud</td>
            <td style="padding: 8px 12px; border-bottom:1px solid #e5e7eb;">{{ $renovacion->fecha_solicitud_renovacion->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; background:#f3f4f6; font-weight:600;">Último vencimiento</td>
            <td style="padding: 8px 12px;">{{ $renovacion->fecha_vencimiento->format('d/m/Y') }}</td>
        </tr>
    </table>

    <p>Ingresa al panel de administración para revisar la solicitud y crear el nuevo ciclo de renovación.</p>
    <br>
    <p style="color: #666; font-size: 12px;">Cliche Business Suite</p>
</div>
