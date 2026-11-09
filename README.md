# Cliche Business Suite

Sistema de gestión para una agencia que vende servicios de posicionamiento local: lleva el control de clientes, renovaciones de servicio, pagos y gastos, y permite que el cliente apruebe las fotografías que se publicarán en su perfil. Lo usan tres perfiles: administrador, operador y cliente.

## Funcionalidades principales

- **Clientes:** alta, edición, baja con restauración, lista de suspendidos y bitácora de cambios de estatus. Las cuentas se activan por correo con un token con vigencia.
- **Renovaciones:** control de vencimientos con periodo de gracia, historial, solicitud de renovación desde el portal del cliente, suspensión y cancelación, y recordatorios automáticos.
- **Aprobación de fotografías:** el operador carga un paquete de fotos y el cliente recibe una notificación para seleccionar y aprobar. Si no responde, las fotos se aprueban automáticamente; las no elegidas pasan a un banco de reserva con fecha de expiración y prioridad. Se avisa al operador cuando las fotos son insuficientes.
- **Calendario de fotos:** programación, reprogramación, cancelación y publicación de fotografías por cliente.
- **Pagos:** el historial se genera desde las renovaciones; el cliente envía su comprobante al renovar y el administrador lo valida o lo rechaza.
- **Gastos operativos:** registro por categoría, con catálogo de categorías.
- **Bóveda de contraseñas:** credenciales de los clientes guardadas cifradas; el acceso pide un código de verificación enviado por correo y queda registrado.
- **Reportes:** cartera, financiero, rentabilidad y gastos operativos.
- **Notificaciones** dentro de la aplicación y por correo.
- **Respaldos** de la base de datos.
- **Seguridad:** roles por ruta, límite de intentos en login y recuperación de contraseña, cierre de sesión por inactividad (30 minutos por defecto, configurable) y recuperación de contraseña con código.

## Capturas

<!-- Agrega aquí tus imágenes, por ejemplo: ![Panel de administración](docs/capturas/panel.png) -->

_Pendiente._

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP ^8.3, Laravel 13 (13.11.2 en `composer.lock`) |
| Base de datos | MySQL 8+ (SQLite en memoria para las pruebas) |
| Frontend | Blade, Tailwind CSS 4, Alpine.js 3, Vite 8, Chart.js 4, Lucide |
| Almacenamiento de fotos | Google Cloud Storage (`google/cloud-storage` 2.3, Flysystem) |
| PDF | barryvdh/laravel-dompdf 3.1 |
| Pruebas | PHPUnit 12 |

## Arquitectura

Aplicación monolítica MVC con Laravel:

- `app/Http/Controllers/`: un controlador por módulo (clientes, renovaciones, aprobaciones, calendario, pagos, gastos, bóveda, reportes, respaldos, notificaciones).
- Rutas agrupadas por rol (`admin`, `operador`, `cliente`) con un middleware de roles y otro de expiración de sesión.
- `app/Models/`: modelos Eloquent (Cliente, ControlRenovacion, PaqueteAprobacion, FotoAprobacion, CalendarioFoto, PagoCliente, GastoOperativo, BovedaContrasena, entre otros).
- `app/Notifications/` y `app/Mail/`: notificaciones internas y correos transaccionales.
- `app/Console/Commands/` programados a diario en `routes/console.php`: `aprobaciones:auto-aprobar`, `aprobaciones:recordatorio-pendientes`, `reservas:expirar` y `renovaciones:verificar`.
- `database/migrations/` (37 migraciones) y `database/seeders/` con datos de demostración.
- `docs/`: diagramas PlantUML y draw.io de los flujos (login, clientes, aprobación de fotografías, renovaciones) y documentación técnica del módulo de aprobación y calendario.

## Instalación y ejecución

Requisitos: PHP 8.3+, Composer 2, Node.js y MySQL 8+ (por ejemplo, con WAMP en Windows).

```bash
git clone https://github.com/LDanielMC/Cliche-Business-Suite.git
cd Cliche-Business-Suite
composer install
cp .env.example .env
php artisan key:generate
npm install --ignore-scripts
```

En `.env`, ajusta la conexión a MySQL (`.env.example` trae `DB_PORT=3308` y la base `cliche_business_suite`; cambia el puerto si el tuyo es otro) y crea la base de datos con cotejamiento `utf8mb4_unicode_ci`. Luego:

```bash
php artisan migrate --seed   # crea las tablas y datos de demostración
composer dev                 # servidor, cola, logs y Vite en paralelo
```

La app queda en `http://localhost:8000`. `composer setup` ejecuta la instalación, las migraciones y `npm run build` en un solo paso.

### Variables y configuración relevantes

| Variable | Uso |
|---|---|
| `DB_*` | Conexión a MySQL |
| `MAIL_*` | Envío de correos (`MAIL_MAILER=log` en desarrollo escribe los correos en el log) |
| `QUEUE_CONNECTION` | Cola de trabajos (`database` por defecto) |
| `SESSION_TIMEOUT_MINUTES` | Minutos de inactividad antes de cerrar la sesión (30 por defecto) |
| `GCS_KEY_FILE`, `GCS_PROJECT_ID`, `GCS_BUCKET`, `GCS_URL`, `GCS_PATH_PREFIX` | Cuenta de servicio, proyecto y bucket de Google Cloud Storage para las fotografías |

El disco `gcs` de `config/filesystems.php` lee la llave de la cuenta de servicio de Google del archivo `storage/app/<GCS_KEY_FILE>`. Ese JSON no se incluye en el repositorio y `.gitignore` excluye `storage/app/*.json`.

### Tareas programadas

Para que corran los comandos diarios, agrega al cron del servidor:

```
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

## Pruebas

```bash
composer test    # limpia la caché de configuración y ejecuta php artisan test
```

Las pruebas son de funcionalidad (PHPUnit) y corren con SQLite en memoria. Cubren aprobación y auto-aprobación de fotos, calendario, expiración de reservas, renovaciones con periodo de gracia, notificaciones y correos, gastos y categorías, bóveda de contraseñas, panel del operador, los cuatro reportes y la consistencia de los seeders.
