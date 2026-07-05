<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <h2 style="color: #667eea;">Tu servicio está por vencer</h2>
    <p>Hola {{ $renovacion->cliente->user->name }},</p>
    <p>Te recordamos que tu servicio de posicionamiento local <strong>{{ $renovacion->cliente->nombre_negocio }}</strong> vence el
        <strong>{{ $renovacion->fecha_vencimiento->format('d/m/Y') }}</strong>.</p>
    <p>Para evitar interrupciones en el servicio, por favor contacta a tu asesor para renovar a tiempo.</p>
    <br>
    <p style="color: #666; font-size: 12px;">Cliche Business Suite</p>
</div>
