<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConversacionesController;
use App\Http\Controllers\FarmaciasController;
use App\Http\Controllers\ManualesController;
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
    Route::get('/conversaciones',            [ConversacionesController::class,'index'])->name('conversaciones');
    Route::get('/conversaciones/descargar',  [ConversacionesController::class,'descargar'])->name('conversaciones.descargar');
    Route::get('/farmacias',        [FarmaciasController::class,    'index'])->name('farmacias');
    Route::get('/rendimiento',      [RendimientoController::class,  'index'])->name('rendimiento');
    Route::get('/usuarios',         [UsuariosController::class,     'index'])->name('usuarios');

    // --- Carga de manuales a la base de conocimiento (n8n -> Qdrant) ---
    // GET  muestra el formulario  ->  ruta con nombre "manuales.create"
    // POST recibe el archivo      ->  ruta con nombre "manuales.store"
    // Ambas usan la misma URL (/manuales); lo que las diferencia es el verbo HTTP.
    Route::get('/manuales',             [ManualesController::class, 'create'])->name('manuales.create');
    Route::post('/manuales',            [ManualesController::class, 'store'])->name('manuales.store');
    // Borra de Qdrant todos los fragmentos de un modulo+version. Irreversible.
    Route::delete('/manuales',          [ManualesController::class, 'destroy'])->name('manuales.destroy');
    // AJAX: devuelve los fragmentos de un módulo+versión para verlos en el modal.
    Route::get('/manuales/fragmentos',  [ManualesController::class, 'fragmentos'])->name('manuales.fragmentos');
    // Guarda la lista de versiones en .env (configurable desde el panel).
    Route::post('/manuales/versiones',      [ManualesController::class, 'guardarVersiones'])->name('manuales.versiones');
    // Cambia la versión de los fragmentos de un módulo directamente en Qdrant.
    Route::post('/manuales/modulo-version', [ManualesController::class, 'cambiarVersionModulo'])->name('manuales.modulo-version');
});
