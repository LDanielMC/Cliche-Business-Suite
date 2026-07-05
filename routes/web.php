<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BovedaContrasenaController;
use App\Http\Controllers\CalendarioFotoController;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\GastoOperativoController;
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
Route::post('/login', [AuthController::class, 'login']);

// Recuperación de contraseña
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('password.email');
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

    // Dashboard de Admin
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('admin.dashboard');

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
        Route::delete('/admin/clientes/{id}/force-delete', [ClienteController::class, 'forceDestroy'])->name('clientes.forceDelete');

        // Gestión de gastos operativos y sus categorías
        Route::get('/admin/categorias-gastos', [CategoriaGastoController::class, 'index'])->name('categorias-gastos.index');
        Route::get('/admin/categorias-gastos/create', [CategoriaGastoController::class, 'create'])->name('categorias-gastos.create');
        Route::post('/admin/categorias-gastos', [CategoriaGastoController::class, 'store'])->name('categorias-gastos.store');
        Route::get('/admin/categorias-gastos/{categoria}/edit', [CategoriaGastoController::class, 'edit'])->name('categorias-gastos.edit');
        Route::put('/admin/categorias-gastos/{categoria}', [CategoriaGastoController::class, 'update'])->name('categorias-gastos.update');
        Route::delete('/admin/categorias-gastos/{categoria}', [CategoriaGastoController::class, 'destroy'])->name('categorias-gastos.destroy');

        Route::get('/admin/gastos', [GastoOperativoController::class, 'index'])->name('gastos.index');
        Route::get('/admin/gastos/create', [GastoOperativoController::class, 'create'])->name('gastos.create');
        Route::post('/admin/gastos', [GastoOperativoController::class, 'store'])->name('gastos.store');
        Route::get('/admin/gastos/{gasto}/edit', [GastoOperativoController::class, 'edit'])->name('gastos.edit');
        Route::put('/admin/gastos/{gasto}', [GastoOperativoController::class, 'update'])->name('gastos.update');
        Route::delete('/admin/gastos/{gasto}', [GastoOperativoController::class, 'destroy'])->name('gastos.destroy');

        // Gestión de pagos de clientes
        Route::get('/admin/pagos', [PagoClienteController::class, 'index'])->name('pagos.index');
        Route::get('/admin/pagos/create', [PagoClienteController::class, 'create'])->name('pagos.create');
        Route::post('/admin/pagos', [PagoClienteController::class, 'store'])->name('pagos.store');
        Route::get('/admin/pagos/{pago}/edit', [PagoClienteController::class, 'edit'])->name('pagos.edit');
        Route::put('/admin/pagos/{pago}', [PagoClienteController::class, 'update'])->name('pagos.update');
        Route::delete('/admin/pagos/{pago}', [PagoClienteController::class, 'destroy'])->name('pagos.destroy');

        // Bóveda de contraseñas
        Route::get('/admin/boveda', [BovedaContrasenaController::class, 'index'])->name('boveda.index');
        Route::get('/admin/boveda/create', [BovedaContrasenaController::class, 'create'])->name('boveda.create');
        Route::post('/admin/boveda', [BovedaContrasenaController::class, 'store'])->name('boveda.store');
        Route::get('/admin/boveda/{credencial}/edit', [BovedaContrasenaController::class, 'edit'])->name('boveda.edit');
        Route::put('/admin/boveda/{credencial}', [BovedaContrasenaController::class, 'update'])->name('boveda.update');
        Route::delete('/admin/boveda/{credencial}', [BovedaContrasenaController::class, 'destroy'])->name('boveda.destroy');

        // Control de renovaciones
        Route::get('/admin/renovaciones', [RenovacionController::class, 'index'])->name('renovaciones.index');
        Route::get('/admin/renovaciones/create', [RenovacionController::class, 'create'])->name('renovaciones.create');
        Route::post('/admin/renovaciones', [RenovacionController::class, 'store'])->name('renovaciones.store');
        Route::post('/admin/renovaciones/{renovacion}/renovar', [RenovacionController::class, 'renovar'])->name('renovaciones.renovar');

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

    // Calendario de fotos (Admin y Operador)
    Route::middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_OPERADOR)->group(function () {
        Route::get('/calendario', [CalendarioFotoController::class, 'index'])->name('calendario.index');
        Route::get('/calendario/create', [CalendarioFotoController::class, 'create'])->name('calendario.create');
        Route::post('/calendario', [CalendarioFotoController::class, 'store'])->name('calendario.store');
        Route::get('/calendario/{calendario}/edit', [CalendarioFotoController::class, 'edit'])->name('calendario.edit');
        Route::put('/calendario/{calendario}', [CalendarioFotoController::class, 'update'])->name('calendario.update');
        Route::delete('/calendario/{calendario}', [CalendarioFotoController::class, 'destroy'])->name('calendario.destroy');

        // Aprobación de fotografía (creación de paquetes y carga de candidatas)
        Route::get('/aprobaciones', [PaqueteAprobacionController::class, 'index'])->name('aprobaciones.index');
        Route::get('/aprobaciones/create', [PaqueteAprobacionController::class, 'create'])->name('aprobaciones.create');
        Route::post('/aprobaciones', [PaqueteAprobacionController::class, 'store'])->name('aprobaciones.store');
        Route::get('/aprobaciones/{paquete}', [PaqueteAprobacionController::class, 'show'])->name('aprobaciones.show');
        Route::delete('/aprobaciones/{paquete}', [PaqueteAprobacionController::class, 'destroy'])->name('aprobaciones.destroy');
        Route::post('/aprobaciones/{paquete}/fotos', [PaqueteAprobacionController::class, 'uploadFotos'])->name('aprobaciones.fotos.store');
        Route::delete('/aprobaciones/{paquete}/fotos/{foto}', [PaqueteAprobacionController::class, 'destroyFoto'])->name('aprobaciones.fotos.destroy');
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

    // Aprobación de fotografía (vista del cliente)
    Route::middleware('role:' . User::ROLE_CLIENTE)->group(function () {
        Route::get('/cliente/aprobaciones', [PaqueteAprobacionController::class, 'misAprobaciones'])->name('cliente.aprobaciones.index');
        Route::get('/cliente/aprobaciones/{paquete}', [PaqueteAprobacionController::class, 'showCliente'])->name('cliente.aprobaciones.show');
        Route::post('/cliente/aprobaciones/{paquete}/confirmar', [PaqueteAprobacionController::class, 'confirmarSeleccion'])->name('cliente.aprobaciones.confirmar');
    });
});
