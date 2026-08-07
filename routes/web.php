<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BovedaContrasenaController;
use App\Http\Controllers\CalendarioFotoController;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\GastoOperativoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\PagoClienteController;
use App\Http\Controllers\PaqueteAprobacionController;
use App\Http\Controllers\RenovacionController;
use App\Http\Controllers\ReporteController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Rutas publicas
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');

// Recuperación de contraseña
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->middleware('throttle:3,1')->name('password.email');
Route::get('/verify-code', [AuthController::class, 'showVerifyCode'])->name('password.verify');
Route::post('/verify-code', [AuthController::class, 'verifyCode'])->name('password.verify.post');
Route::get('/reset-password', [AuthController::class, 'showNewPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Activación de cuenta para clientes nuevos
Route::get('/activar-cuenta/{token}', [AuthController::class, 'showActivationForm'])->name('activate.show');
Route::post('/activar-cuenta/{token}', [AuthController::class, 'setPassword'])->name('activate.setPassword');

// Rutas protegidas con autenticacion y timeout de sesion
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Centro de notificaciones — disponible para cualquier rol autenticado
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcar-todas');
    Route::delete('/notificaciones/leidas', [NotificacionController::class, 'eliminarLeidas'])->name('notificaciones.eliminar-leidas');
    Route::delete('/notificaciones/{id}', [NotificacionController::class, 'destroy'])->name('notificaciones.destroy');

    // Dashboard de Admin
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('admin.dashboard');

    // Test de Diseño Moderno
    Route::get('/test-design', function () {
        return view('test-design');
    })->middleware('role:' . User::ROLE_ADMIN)->name('test.design');

    // Diagnóstico del Sistema
    Route::get('/diagnostic', function () {
        return view('diagnostic');
    })->middleware('role:' . User::ROLE_ADMIN)->name('system.diagnostic');

    // Verificación del Sistema
    Route::get('/verify-system', function () {
        return view('verify-system');
    })->middleware('role:' . User::ROLE_ADMIN)->name('system.verify');

    // Gestión de Clientes (solo Admin)
    Route::middleware('role:' . User::ROLE_ADMIN)->group(function () {
        Route::get('/admin/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/admin/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/admin/clientes', [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('/admin/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::get('/admin/clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/admin/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
        Route::delete('/admin/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
        Route::get('/admin/clientes-eliminados', [ClienteController::class, 'eliminados'])->name('clientes.eliminados');
        Route::post('/admin/clientes/{id}/restaurar', [ClienteController::class, 'restaurar'])->name('clientes.restaurar');
        Route::get('/admin/clientes-suspendidos', [ClienteController::class, 'suspendidos'])->name('clientes.suspendidos');

        // Categorías de gastos (exclusivo admin — los gastos en sí los puede
        // registrar también el operador, ver grupo role:admin,operador abajo)
        Route::get('/admin/categorias-gastos', [CategoriaGastoController::class, 'index'])->name('categorias-gastos.index');
        Route::get('/admin/categorias-gastos/create', [CategoriaGastoController::class, 'create'])->name('categorias-gastos.create');
        Route::post('/admin/categorias-gastos', [CategoriaGastoController::class, 'store'])->name('categorias-gastos.store');
        Route::get('/admin/categorias-gastos/{categoria}/edit', [CategoriaGastoController::class, 'edit'])->name('categorias-gastos.edit');
        Route::put('/admin/categorias-gastos/{categoria}', [CategoriaGastoController::class, 'update'])->name('categorias-gastos.update');
        Route::delete('/admin/categorias-gastos/{categoria}', [CategoriaGastoController::class, 'destroy'])->name('categorias-gastos.destroy');

        // Historial de pagos (solo lectura — los pagos se generan automáticamente desde Renovaciones)
        Route::get('/admin/pagos', [PagoClienteController::class, 'index'])->name('pagos.index');

        // Bóveda de contraseñas
        Route::get('/admin/boveda', [BovedaContrasenaController::class, 'index'])->name('boveda.index');
        Route::get('/admin/boveda/create', [BovedaContrasenaController::class, 'create'])->name('boveda.create');
        Route::post('/admin/boveda', [BovedaContrasenaController::class, 'store'])->name('boveda.store');
        Route::get('/admin/boveda/{credencial}/edit', [BovedaContrasenaController::class, 'edit'])->name('boveda.edit');
        Route::put('/admin/boveda/{credencial}', [BovedaContrasenaController::class, 'update'])->name('boveda.update');
        Route::delete('/admin/boveda/{credencial}', [BovedaContrasenaController::class, 'destroy'])->name('boveda.destroy');
        Route::post('/admin/boveda/{credencial}/revelar', [BovedaContrasenaController::class, 'revelar'])
            ->middleware('throttle:20,1')
            ->name('boveda.revelar');
        Route::post('/admin/boveda/verificar/enviar-codigo', [BovedaContrasenaController::class, 'enviarCodigo'])
            ->middleware('throttle:5,1')
            ->name('boveda.verificar.enviar-codigo');
        Route::post('/admin/boveda/verificar/confirmar-codigo', [BovedaContrasenaController::class, 'confirmarCodigo'])
            ->middleware('throttle:10,1')
            ->name('boveda.verificar.confirmar-codigo');

        // Control de renovaciones (admin)
        Route::get('/admin/renovaciones', [RenovacionController::class, 'index'])->name('renovaciones.index');
        Route::get('/admin/renovaciones/create', [RenovacionController::class, 'create'])->name('renovaciones.create');
        Route::get('/admin/renovaciones/create/{renovacion}', [RenovacionController::class, 'createDesdeSolicitud'])->name('renovaciones.create.solicitud');
        Route::post('/admin/renovaciones', [RenovacionController::class, 'store'])->name('renovaciones.store');
        Route::get('/admin/renovaciones/{renovacion}', [RenovacionController::class, 'show'])->name('renovaciones.show');
        Route::post('/admin/renovaciones/{renovacion}/validar', [RenovacionController::class, 'validarPago'])->name('renovaciones.validar');
        Route::post('/admin/renovaciones/{renovacion}/rechazar', [RenovacionController::class, 'rechazarPago'])->name('renovaciones.rechazar');
        Route::post('/admin/renovaciones/{renovacion}/factura', [RenovacionController::class, 'cargarFactura'])->name('renovaciones.factura');

        // Reportes financieros
        Route::get('/admin/reportes/financiero', [ReporteController::class, 'financiero'])->name('reportes.financiero');
        Route::post('/admin/reportes/financiero/pdf', [ReporteController::class, 'financieroPdf'])->name('reportes.financiero.pdf');
        Route::get('/admin/reportes/rentabilidad', [ReporteController::class, 'clientesRentables'])->name('reportes.rentabilidad');
        Route::get('/admin/reportes/cartera', [ReporteController::class, 'evolucionCartera'])->name('reportes.cartera');
        Route::get('/admin/reportes/gastos', [ReporteController::class, 'gastosOperativos'])->name('reportes.gastos');
        Route::post('/admin/reportes/gastos/pdf', [ReporteController::class, 'gastosOperativosPdf'])->name('reportes.gastos.pdf');

        // Respaldo y recuperación de base de datos
        Route::get('/admin/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/admin/backups', [BackupController::class, 'generar'])->name('backups.generar');
        Route::get('/admin/backups/{filename}/descargar', [BackupController::class, 'descargar'])->name('backups.descargar');
        Route::delete('/admin/backups/{filename}', [BackupController::class, 'eliminar'])->name('backups.eliminar');
        Route::post('/admin/backups/{filename}/restaurar', [BackupController::class, 'restaurar'])->name('backups.restaurar');

        // Gestión de Operadores
        Route::get('/admin/operadores', [OperadorController::class, 'index'])->name('operadores.index');
        Route::get('/admin/operadores/create', [OperadorController::class, 'create'])->name('operadores.create');
        Route::post('/admin/operadores', [OperadorController::class, 'store'])->name('operadores.store');
        Route::get('/admin/operadores/{operador}', [OperadorController::class, 'show'])->name('operadores.show');
        Route::get('/admin/operadores/{operador}/edit', [OperadorController::class, 'edit'])->name('operadores.edit');
        Route::put('/admin/operadores/{operador}', [OperadorController::class, 'update'])->name('operadores.update');
        Route::delete('/admin/operadores/{operador}', [OperadorController::class, 'destroy'])->name('operadores.destroy');
    });

    // Calendario de fotos, Aprobaciones y Gastos (Admin y Operador)
    Route::middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_OPERADOR)->group(function () {
        // Gastos operativos — el operador puede registrar y ver, pero solo
        // editar/eliminar los que él mismo registró (control en el controlador).
        Route::get('/admin/gastos', [GastoOperativoController::class, 'index'])->name('gastos.index');
        Route::get('/admin/gastos/create', [GastoOperativoController::class, 'create'])->name('gastos.create');
        Route::post('/admin/gastos', [GastoOperativoController::class, 'store'])->name('gastos.store');
        Route::get('/admin/gastos/{gasto}/edit', [GastoOperativoController::class, 'edit'])->name('gastos.edit');
        Route::put('/admin/gastos/{gasto}', [GastoOperativoController::class, 'update'])->name('gastos.update');
        Route::delete('/admin/gastos/{gasto}', [GastoOperativoController::class, 'destroy'])->name('gastos.destroy');

        Route::get('/calendario', [CalendarioFotoController::class, 'index'])->name('calendario.index');
        Route::get('/calendario/create', [CalendarioFotoController::class, 'create'])->name('calendario.create');
        Route::post('/calendario', [CalendarioFotoController::class, 'store'])->name('calendario.store');
        Route::get('/calendario/{calendario}/edit', [CalendarioFotoController::class, 'edit'])->name('calendario.edit');
        Route::put('/calendario/{calendario}', [CalendarioFotoController::class, 'update'])->name('calendario.update');
        Route::delete('/calendario/{calendario}', [CalendarioFotoController::class, 'destroy'])->name('calendario.destroy');
        // FN.04 — Colocación manual, movimiento y publicación
        Route::post('/calendario/colocar', [CalendarioFotoController::class, 'colocar'])->name('calendario.colocar');
        Route::patch('/calendario/{calendario}/mover', [CalendarioFotoController::class, 'mover'])->name('calendario.mover');
        Route::post('/calendario/{calendario}/publicar', [CalendarioFotoController::class, 'publicar'])->name('calendario.publicar');
        // Calendario por cliente con historial de períodos
        Route::get('/calendario/cliente/{cliente}', [CalendarioFotoController::class, 'clienteView'])->name('calendario.cliente-view');

        // Aprobación de fotografía (FN.08 — gestión de paquetes y Banco de Reserva)
        Route::get('/aprobaciones', [PaqueteAprobacionController::class, 'index'])->name('aprobaciones.index');
        Route::get('/aprobaciones/create', [PaqueteAprobacionController::class, 'create'])->name('aprobaciones.create');
        Route::post('/aprobaciones', [PaqueteAprobacionController::class, 'store'])->name('aprobaciones.store');
        Route::get('/aprobaciones/{paquete}', [PaqueteAprobacionController::class, 'show'])->name('aprobaciones.show');
        Route::delete('/aprobaciones/{paquete}', [PaqueteAprobacionController::class, 'destroy'])->name('aprobaciones.destroy');
        // Fotos candidatas
        Route::post('/aprobaciones/{paquete}/fotos', [PaqueteAprobacionController::class, 'uploadFotos'])->name('aprobaciones.fotos.store');
        Route::delete('/aprobaciones/{paquete}/fotos/{foto}', [PaqueteAprobacionController::class, 'destroyFoto'])->name('aprobaciones.fotos.destroy');
        Route::patch('/aprobaciones/{paquete}/fotos/{foto}/prioridad', [PaqueteAprobacionController::class, 'asignarPrioridad'])->name('aprobaciones.fotos.prioridad');
        // Envío al cliente
        Route::post('/aprobaciones/{paquete}/enviar-cliente', [PaqueteAprobacionController::class, 'enviarACliente'])->name('aprobaciones.enviar-cliente');
        // Banco de Reserva — reingreso al borrador
        Route::get('/aprobaciones/{paquete}/reserva', [PaqueteAprobacionController::class, 'reserva'])->name('aprobaciones.reserva');
        Route::post('/aprobaciones/{paquete}/reserva/{foto}/reingresar', [PaqueteAprobacionController::class, 'reingresarFoto'])->name('aprobaciones.reserva.reingresar');
    });

    // Dashboard de Operador
    Route::get('/operador/dashboard', [AuthController::class, 'operadorDashboard'])
        ->middleware('role:' . User::ROLE_OPERADOR)
        ->name('operador.dashboard');

    // Dashboard de Cliente
    Route::get('/cliente/dashboard', [AuthController::class, 'clienteDashboard'])
        ->middleware('role:' . User::ROLE_CLIENTE)
        ->name('cliente.dashboard');

    Route::get('/cliente/perfil', [ClienteController::class, 'perfil'])
        ->middleware('role:' . User::ROLE_CLIENTE)
        ->name('clientes.perfil');

    Route::get('/cliente/pagos', [PagoClienteController::class, 'misPagos'])
        ->middleware('role:' . User::ROLE_CLIENTE)
        ->name('pagos.misPagos');

    // Calendario del cliente (solo lectura)
    Route::get('/cliente/calendario', [CalendarioFotoController::class, 'clienteCalendario'])
        ->middleware('role:' . User::ROLE_CLIENTE)
        ->name('calendario.cliente');

    // Aprobación de fotografía (vista del cliente)
    Route::middleware('role:' . User::ROLE_CLIENTE)->group(function () {
        Route::get('/cliente/aprobaciones', [PaqueteAprobacionController::class, 'misAprobaciones'])->name('cliente.aprobaciones.index');
        Route::get('/cliente/aprobaciones/{paquete}', [PaqueteAprobacionController::class, 'showCliente'])->name('cliente.aprobaciones.show');
        Route::post('/cliente/aprobaciones/{paquete}/confirmar', [PaqueteAprobacionController::class, 'confirmarSeleccion'])->name('cliente.aprobaciones.confirmar');

        // Renovaciones (cliente)
        Route::get('/cliente/renovaciones', [RenovacionController::class, 'miRenovacion'])->name('renovaciones.cliente.index');
        Route::get('/cliente/renovaciones/{renovacion}/renovar', [RenovacionController::class, 'formRenovar'])->name('renovaciones.cliente.renovar');
        Route::post('/cliente/renovaciones/{renovacion}/enviar', [RenovacionController::class, 'enviarComprobante'])->name('renovaciones.cliente.enviar');
        Route::post('/cliente/renovaciones/{renovacion}/solicitar', [RenovacionController::class, 'solicitarRenovacion'])->name('renovaciones.cliente.solicitar');
    });
});
