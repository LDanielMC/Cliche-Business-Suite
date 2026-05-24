<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Rutas publicas
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con autenticacion y timeout de sesion
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard de Admin
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])
        ->middleware('role:' . User::ROLE_ADMIN)
        ->name('admin.dashboard');

    // Dashboard de Supervisor
    Route::get('/supervisor/dashboard', [AuthController::class, 'supervisorDashboard'])
        ->middleware('role:' . User::ROLE_SUPERVISOR)
        ->name('supervisor.dashboard');

    // Dashboard de Cliente
    Route::get('/cliente/dashboard', [AuthController::class, 'clienteDashboard'])
        ->middleware('role:' . User::ROLE_CLIENTE)
        ->name('cliente.dashboard');
});
