<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OperadorController;
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

        // Gestión de Operadores
        Route::get('/admin/operadores', [OperadorController::class, 'index'])->name('operadores.index');
        Route::get('/admin/operadores/create', [OperadorController::class, 'create'])->name('operadores.create');
        Route::post('/admin/operadores', [OperadorController::class, 'store'])->name('operadores.store');
        Route::get('/admin/operadores/{operador}', [OperadorController::class, 'show'])->name('operadores.show');
        Route::get('/admin/operadores/{operador}/edit', [OperadorController::class, 'edit'])->name('operadores.edit');
        Route::put('/admin/operadores/{operador}', [OperadorController::class, 'update'])->name('operadores.update');
        Route::delete('/admin/operadores/{operador}', [OperadorController::class, 'destroy'])->name('operadores.destroy');
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
});
