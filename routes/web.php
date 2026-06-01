<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConversacionesController;
use App\Http\Controllers\FarmaciasController;
use App\Http\Controllers\RendimientoController;
use App\Http\Controllers\UsuariosController;

/*
|--------------------------------------------------------------------------
| Web Routes - Panel IA iCompras360
|--------------------------------------------------------------------------
*/

// Redirigir raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas protegidas (requieren sesión iniciada)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',        [DashboardController::class,    'index'])->name('dashboard');
    Route::get('/conversaciones',   [ConversacionesController::class,'index'])->name('conversaciones');
    Route::get('/farmacias',        [FarmaciasController::class,    'index'])->name('farmacias');
    Route::get('/rendimiento',      [RendimientoController::class,  'index'])->name('rendimiento');
    Route::get('/usuarios',         [UsuariosController::class,     'index'])->name('usuarios');
});
