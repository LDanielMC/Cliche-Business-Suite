<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Días de gracia tras el vencimiento
    |--------------------------------------------------------------------------
    | Días extra que el cliente tiene para pagar sin ser suspendido.
    | Dentro de este período el cliente sigue activo y su renovación se
    | retoma desde la fecha de vencimiento (sin cobrar el hueco).
    */
    'dias_gracia' => (int) env('RENOVACIONES_DIAS_GRACIA', 5),

    /*
    |--------------------------------------------------------------------------
    | Vigencia del Banco de Reserva (meses)
    |--------------------------------------------------------------------------
    | Tiempo en meses que una foto en estado "conservada" permanece disponible
    | antes de que el comando ExpirarReservas notifique al operador.
    */
    'meses_reserva' => (int) env('RENOVACIONES_MESES_RESERVA', 6),

    /*
    |--------------------------------------------------------------------------
    | Límite de veces que una foto puede volver a "conservada"
    |--------------------------------------------------------------------------
    | Cada vez que una foto se marca "conservada" (el cliente elige guardarla
    | en vez de aprobarla/descartarla, o sobra en la auto-aprobación) sube un
    | contador. Al llegar a este número, en vez de conservarla otra vez se
    | marca "descartada" — evita que quede dando vueltas para siempre sin que
    | nadie tome una decisión final sobre ella.
    */
    'max_veces_conservada' => (int) env('RENOVACIONES_MAX_VECES_CONSERVADA', 3),

    /*
    |--------------------------------------------------------------------------
    | Días de plazo para que el cliente revise un paquete
    |--------------------------------------------------------------------------
    | Al enviar un paquete al cliente, fecha_limite = now + dias_plazo_aprobacion.
    */
    'dias_plazo_aprobacion' => (int) env('RENOVACIONES_DIAS_PLAZO_APROBACION', 3),

    /*
    |--------------------------------------------------------------------------
    | Destino por defecto de las fotos sobrantes en auto-aprobación
    |--------------------------------------------------------------------------
    | Cuando AutoAprobarFotos necesita desechar fotos no seleccionadas,
    | las envía aquí. Valores permitidos: 'conservada' | 'descartada'.
    */
    'destino_sobrantes_auto' => env('RENOVACIONES_DESTINO_SOBRANTES', 'conservada'),

    /*
    |--------------------------------------------------------------------------
    | Alertas de paquete de aprobación sin enviar
    |--------------------------------------------------------------------------
    | Si a un cliente le quedan "dias_aviso_temprano" días o menos para que
    | termine su período de renovación y todavía no se le ha enviado un
    | paquete de aprobación (sigue en borrador o no existe), se muestra un
    | aviso en el listado y se manda un recordatorio por correo.
    | "dias_aviso_critico" marca el límite duro: por debajo de este número
    | de días ya no queda tiempo razonable para que el cliente revise y
    | apruebe sus fotos, así que el aviso escala a "urgente".
    */
    'dias_aviso_temprano' => (int) env('RENOVACIONES_DIAS_AVISO_TEMPRANO', 25),
    'dias_aviso_critico'  => (int) env('RENOVACIONES_DIAS_AVISO_CRITICO', 15),

];
