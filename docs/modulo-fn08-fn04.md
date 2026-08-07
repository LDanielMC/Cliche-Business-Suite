# Módulo FN.08 / FN.04 — Documentación técnica de cierre

> Generado: 2026-07-11  
> Estado: **Aprobado y cerrado** (pendiente: Paso 5 — suite de tests)

---

## 1. Alcance

| Código | Nombre | Estado |
|--------|--------|--------|
| FN.08  | Aprobación de fotografías | ✅ Implementado |
| FN.04  | Calendario de fotos (Paso 3) | ✅ Implementado |
| —      | Ajustes a Renovaciones | ✅ Implementado |
| Paso 5 | Suite de tests funcionales (14 escenarios) | ⏳ Pendiente |

---

## 2. Migraciones aplicadas (en orden)

### `2026_07_10_100001_add_fn08_fields_to_fotos_aprobacion_table`
Tabla: `fotos_aprobacion`

| Columna nueva | Tipo | Notas |
|---|---|---|
| `prioridad` | `unsignedInteger` nullable | Orden de preferencia del operador |
| `fecha_ingreso_reserva` | `date` nullable | Cuándo entró al Banco de Reserva |
| `fecha_expiracion_reserva` | `date` nullable | Caducidad en el Banco de Reserva |

Índice nuevo: `UNIQUE (paquete_aprobacion_id, prioridad)` — impide dos fotos con la misma prioridad en el mismo paquete.

---

### `2026_07_10_100002_update_paquetes_aprobacion_for_fn08`
Tabla: `paquetes_aprobacion`

| Cambio | Detalle |
|---|---|
| ENUM `estatus` | Añade `'borrador'` (ahora default) |
| `fecha_envio` | `date` nullable — se llena en `enviarACliente()` |
| `fecha_limite` | `date` nullable — se llena en `enviarACliente()` |
| `motivo_finalizacion` | ENUM nullable: `aprobada_por_cliente`, `aprobada_automaticamente` |
| `fecha_inicio_periodo` | `date` nullable — snapshot de renovación |
| `fecha_vencimiento_periodo` | `date` nullable — snapshot de renovación |
| Índice eliminado | `UNIQUE(cliente_id, mes_revision)` (antes del drop se agrega plain index en `cliente_id`) |
| Columna generada | `cliente_id_activo BIGINT VIRTUAL = IF(estatus IN ('borrador','pendiente'), cliente_id, NULL)` |
| Índice único | `uq_un_paquete_activo_por_cliente` sobre `cliente_id_activo` |

Invariante de BD: un cliente solo puede tener UN paquete en borrador o pendiente a la vez.

---

### `2026_07_11_100001_add_foto_aprobacion_fk_to_calendario_fotos_table`
Tabla: `calendario_fotos`

| Cambio | Detalle |
|---|---|
| `foto_aprobacion_id` | `unsignedBigInteger` nullable, FK → `fotos_aprobacion.id` nullOnDelete |
| Backfill legacy | UPDATE JOIN por `ruta_foto` (one-time, best-effort para datos pre-FK) |
| `fecha_activa` | `DATE VIRTUAL = IF(estatus IN ('programada','publicada'), DATE(fecha_pub_prog), NULL)` |
| `foto_activa_id` | `BIGINT VIRTUAL = IF(estatus IN ('programada','publicada'), foto_aprobacion_id, NULL)` |
| Índice único | `uq_un_activa_por_cliente_dia` sobre `(cliente_id, fecha_activa)` |
| Índice único | `uq_foto_activa` sobre `foto_activa_id` |

**⚠️ Antes de correr en staging/producción:**
```sql
-- Query 1: filas que quedarán con FK NULL después del backfill
SELECT COUNT(*) AS quedaran_null
FROM calendario_fotos cf
LEFT JOIN fotos_aprobacion fa ON fa.ruta_foto = cf.fotografia_asociada
WHERE fa.id IS NULL;

-- Query 2: rutas ambiguas (una ruta → más de una foto_aprobacion)
SELECT cf.id AS calendario_id, cf.fotografia_asociada, COUNT(fa.id) AS matches
FROM calendario_fotos cf
JOIN fotos_aprobacion fa ON fa.ruta_foto = cf.fotografia_asociada
GROUP BY cf.id, cf.fotografia_asociada
HAVING matches > 1;
```
- Query 1 N > 0 → borrar si son demo, poblar FK a mano si son reales.
- Query 2 filas → resolver ambigüedad manualmente antes de migrar.

---

## 3. Modelos modificados

### `PaqueteAprobacion`
```
Constantes nuevas:
  ESTATUS_BORRADOR      = 'borrador'
  ESTATUS_ACTIVOS       = ['borrador', 'pendiente']
  MOTIVO_APROBADO_CLIENTE    = 'aprobada_por_cliente'
  MOTIVO_APROBADO_AUTOMATICO = 'aprobada_automaticamente'

Fillable añadido: motivo_finalizacion, fecha_inicio_periodo, fecha_vencimiento_periodo
Casts añadidos:   fecha_inicio_periodo (date), fecha_vencimiento_periodo (date)
Eliminado:        publicarEnCalendario() — no existe en ningún flujo
```

### `FotoAprobacion`
```
Fillable añadido: prioridad, fecha_ingreso_reserva, fecha_expiracion_reserva
Casts añadidos:   fecha_ingreso_reserva (date), fecha_expiracion_reserva (date)
```

### `CalendarioFoto`
```
Fillable añadido: foto_aprobacion_id
Relación nueva:   fotoAprobacion() → BelongsTo FotoAprobacion
```

---

## 4. Nuevos archivos de aplicación

### Mailables
| Clase | Vista | Cuándo se envía |
|---|---|---|
| `App\Mail\PaqueteEnviadoCliente` | `emails.paquete-enviado-cliente` | Al pasar borrador → pendiente |
| `App\Mail\AdvertenciaFotosInsuficientes` | `emails.advertencia-fotos-insuficientes` | Auto-aprobación con fotos faltantes o sin prioridad |
| `App\Mail\NotificacionExpirarReserva` | `emails.notificacion-expirar-reserva` | Al detectar fotos conservadas caducadas |

### Comandos Artisan
| Signature | Clase | Función |
|---|---|---|
| `aprobaciones:auto-aprobar` | `AutoAprobarFotos` | Auto-aprueba por prioridad paquetes vencidos; sobrantes → Banco de Reserva |
| `reservas:expirar` | `ExpirarReservas` | Notifica admin/operador de fotos conservadas por vencer; NO borra |
| `renovaciones:verificar` | `VerificarRenovaciones` | Suspende clientes (con gracia de 5 días); sin pausa de fotos |

### Config
**`config/renovaciones.php`** — parámetros de negocio sobreridables por .env:

| Clave | Default | Uso |
|---|---|---|
| `dias_gracia` | 5 | Días post-vencimiento antes de suspender |
| `meses_reserva` | 6 | Vigencia de fotos en Banco de Reserva |
| `dias_plazo_aprobacion` | 3 | Plazo que tiene el cliente para seleccionar |
| `destino_sobrantes_auto` | `'conservada'` | Estatus de sobrantes en auto-aprobación |

---

## 5. Rutas nuevas y modificadas

### `routes/console.php`
```php
Schedule::command('aprobaciones:auto-aprobar')->daily();
Schedule::command('reservas:expirar')->daily();
Schedule::command('renovaciones:verificar')->daily();
```

### `routes/web.php` — Aprobaciones (role: admin, operador)
| Verbo | URI | Nombre | Descripción |
|---|---|---|---|
| GET | `/aprobaciones` | `aprobaciones.index` | Listado paquetes |
| GET | `/aprobaciones/create` | `aprobaciones.create` | Formulario nuevo paquete |
| POST | `/aprobaciones` | `aprobaciones.store` | Crear en borrador |
| GET | `/aprobaciones/{paquete}` | `aprobaciones.show` | Ver paquete |
| DELETE | `/aprobaciones/{paquete}` | `aprobaciones.destroy` | Borrar borrador |
| POST | `/aprobaciones/{paquete}/fotos` | `aprobaciones.fotos.store` | Subir fotos candidatas |
| DELETE | `/aprobaciones/{paquete}/fotos/{foto}` | `aprobaciones.fotos.destroy` | Eliminar foto candidata |
| PATCH | `/aprobaciones/{paquete}/fotos/{foto}/prioridad` | `aprobaciones.fotos.prioridad` | Asignar prioridad |
| POST | `/aprobaciones/{paquete}/enviar-cliente` | `aprobaciones.enviar-cliente` | Borrador → pendiente |
| GET | `/aprobaciones/{paquete}/reserva` | `aprobaciones.reserva` | Ver Banco de Reserva del cliente |
| POST | `/aprobaciones/{paquete}/reserva/{foto}/reingresar` | `aprobaciones.reserva.reingresar` | Reingresar foto al borrador |

### `routes/web.php` — Aprobaciones (role: cliente)
| Verbo | URI | Nombre | Descripción |
|---|---|---|---|
| GET | `/cliente/aprobaciones` | `cliente.aprobaciones.index` | Mis paquetes |
| GET | `/cliente/aprobaciones/{paquete}` | `cliente.aprobaciones.show` | Ver paquete pendiente |
| POST | `/cliente/aprobaciones/{paquete}/confirmar` | `cliente.aprobaciones.confirmar` | Confirmar selección exacta |

### `routes/web.php` — Calendario (role: admin, operador)
| Verbo | URI | Nombre | Descripción |
|---|---|---|---|
| POST | `/calendario/colocar` | `calendario.colocar` | Colocar foto aprobada en un día |
| PATCH | `/calendario/{calendario}/mover` | `calendario.mover` | Mover entrada programada a nueva fecha |
| POST | `/calendario/{calendario}/publicar` | `calendario.publicar` | programada → publicada |

### `routes/web.php` — Calendario (role: cliente)
| Verbo | URI | Nombre | Descripción |
|---|---|---|---|
| GET | `/cliente/calendario` | `calendario.cliente` | Vista solo lectura |

---

## 6. Flujo completo de estados

### FotoAprobacion
```
pendiente ──[operador sube]──────────────────────────────────── pendiente
pendiente ──[cliente confirma: aprobada]──────────────────────► aprobada
pendiente ──[cliente confirma: conservar]─────────────────────► conservada (+fechas)
pendiente ──[cliente confirma: resto]─────────────────────────► descartada
pendiente ──[auto-aprobación: top N por prioridad]────────────► aprobada
pendiente ──[auto-aprobación: sobrantes]──────────────────────► conservada (+fechas)
conservada ──[operador reingresa a nuevo borrador]────────────► pendiente (sin prioridad)
aprobada ──[operador coloca en calendario]────────────────────► aprobada (CalendarioFoto: programada)
aprobada ──[otra foto gana en confirmarSeleccion]─────────────► descartada/conservada
                                                                 CalendarioFoto → cancelada (cascade)
```

### PaqueteAprobacion
```
borrador ──[operador: enviarACliente()]──────────────────────► pendiente
pendiente ──[cliente: confirmarSeleccion()]──────────────────► completado (motivo: aprobada_por_cliente)
pendiente ──[cron vencido: auto-aprobar]─────────────────────► auto_aprobado (motivo: aprobada_automaticamente)
```

### CalendarioFoto
```
programada ──[operador: publicar()]──────────────────────────► publicada  (irreversible)
programada ──[operador: mover()]─────────────────────────────► programada (nueva fecha)
programada ──[cascade: foto pierde aprobada]─────────────────► cancelada
programada ──[operador: destroy()]───────────────────────────► (eliminado)
publicada  ──[ninguna acción permitida]──────────────────────  (bloqueada)
```

---

## 7. Invariantes de BD garantizadas por columnas generadas

| Tabla | Invariante | Mecanismo |
|---|---|---|
| `paquetes_aprobacion` | Un cliente no puede tener dos paquetes en borrador/pendiente | `UNIQUE(cliente_id_activo)` — columna virtual que es NULL para completado/auto_aprobado |
| `fotos_aprobacion` | No hay dos fotos con la misma prioridad en el mismo paquete | `UNIQUE(paquete_aprobacion_id, prioridad)` |
| `calendario_fotos` | Un cliente no puede tener dos fotos activas el mismo día | `UNIQUE(cliente_id, fecha_activa)` — columna virtual NULL para cancelada/pausada |
| `calendario_fotos` | Una foto aprobada no puede aparecer dos veces activa en el calendario | `UNIQUE(foto_activa_id)` — columna virtual NULL para inactivas |

---

## 8. Integración con Clientes y Renovaciones

### Chequeos en store() (crear paquete)
1. `cliente.user.estatus === activo`
2. No existe paquete en ESTATUS_ACTIVOS para ese cliente (guard de código; BD lo refuerza)
3. `cliente.renovacionActiva` existe
4. `renovacion.estatus IN [vigente, por_vencer]`
5. Snapshot copiado: `fecha_inicio_periodo = renovacion.fecha_inicio`, `fecha_vencimiento_periodo = renovacion.fecha_vencimiento`

### Chequeos en colocar() (programar foto)
1. `foto.estatus === aprobada`
2. Foto sin entrada activa en calendario (por `foto_aprobacion_id`)
3. Fecha dentro del snapshot del paquete de esa foto
4. Día libre para el cliente
5. `cliente.user.estatus === activo`
6. `renovacionActiva.estatus IN [vigente, por_vencer]`
7. Entradas activas en período < `cantidad_requerida`

### Ajustes a VerificarRenovaciones
- Suspensión ahora es a `fecha_vencimiento + dias_gracia (5) < hoy`
- Eliminada toda lógica de pausa de fotos (programada → pausada)
- Reinicio de período en `completarRenovacion()` es grace-aware: si `fecha_pago_cliente <= fecha_vencimiento + dias_gracia`, el nuevo período arranca desde el día siguiente al vencimiento (sin penalización); si no, arranca desde `fecha_pago_cliente`.

### CheckRole — excepción para suspendidos
Los clientes suspendidos (solo ellos) pueden acceder a:
- `renovaciones.cliente.index`
- `renovaciones.cliente.renovar`
- `renovaciones.cliente.enviar`

Todo lo demás los redirige al login con mensaje explicativo.

---

## 9. Pendiente — Paso 5

Los siguientes 14 escenarios funcionales están especificados pero **sin test de código**:

1. Crear paquete con cliente activo y renovación vigente → borrador
2. Crear paquete con renovación vencida → rechazado
3. Crear paquete con paquete activo existente → rechazado (código + BD)
4. Subir fotos a borrador → ok; subir a pendiente → rechazado
5. Asignar prioridad duplicada → rechazado con mensaje claro
6. enviarACliente() con fotos < cantidad_requerida → rechazado
7. enviarACliente() con foto sin prioridad → rechazado
8. confirmarSeleccion() con count ≠ cantidad_requerida → rechazado
9. confirmarSeleccion() IDOR — IDs de otro paquete → filtrados por intersect
10. AutoAprobarFotos: paquete vencido con prioridades completas → auto_aprobado, sobrantes → conservada
11. AutoAprobarFotos: foto sin prioridad → notifica operador, no modifica paquete
12. colocar() foto fuera del período snapshot → rechazado
13. colocar() dos fotos el mismo día para el mismo cliente → segunda rechazada (código + BD)
14. Cascade: foto aprobada con CalendarioFoto activa, cliente la no-aprueba → CalendarioFoto cancelada
