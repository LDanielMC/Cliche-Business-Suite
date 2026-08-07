<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h2>Tienes fotografías pendientes de selección</h2>

    <p>Hola <strong>{{ $paquete->cliente->user->nombre_completo }}</strong>,</p>

    <p>
        Hemos preparado las fotografías candidatas para el período
        <strong>{{ $paquete->mes_revision_legible }}</strong>.
        Necesitas seleccionar exactamente <strong>{{ $paquete->cantidad_requerida }}</strong>
        {{ $paquete->cantidad_requerida === 1 ? 'fotografía' : 'fotografías' }}.
    </p>

    <p>Tienes hasta el <strong>{{ $paquete->fecha_limite->format('d/m/Y') }}</strong> para hacer tu selección.</p>

    <p style="margin: 32px 0;">
        <a href="{{ route('cliente.aprobaciones.show', $paquete) }}"
           style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;">
            Ver mis fotografías
        </a>
    </p>

    <p style="color:#666;font-size:13px;">
        Si no realizas la selección antes de la fecha límite, el sistema elegirá automáticamente
        las {{ $paquete->cantidad_requerida }} fotografías prioritarias por ti.
    </p>

    <hr style="border:none;border-top:1px solid #eee;margin:32px 0;">
    <p style="color:#999;font-size:12px;">Cliche Business Suite</p>
</body>
</html>
