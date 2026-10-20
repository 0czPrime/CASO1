<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\EntradaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EntradaController::class, 'index'])->name('entradas.index');
Route::get('/entradas/{entrada}', [EntradaController::class, 'show'])->whereNumber('entrada')->name('entradas.show');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registrarUsuario'])->name('registro.guardar');
    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'iniciarSesion'])->middleware('throttle:10,1')->name('login.entrar');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'cerrarSesion'])->name('logout');

    Route::get('/entradas/crear', [EntradaController::class, 'create'])->name('entradas.create');
    Route::post('/entradas', [EntradaController::class, 'store'])->name('entradas.store');
    Route::get('/entradas/{entrada}/editar', [EntradaController::class, 'edit'])->whereNumber('entrada')->name('entradas.edit');
    Route::put('/entradas/{entrada}', [EntradaController::class, 'update'])->whereNumber('entrada')->name('entradas.update');
    Route::delete('/entradas/{entrada}', [EntradaController::class, 'destroy'])->whereNumber('entrada')->name('entradas.destroy');

    Route::post('/entradas/{entrada}/comentarios', [ComentarioController::class, 'store'])->whereNumber('entrada')->name('comentarios.store');
});
