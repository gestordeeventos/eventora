<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventoController as AdminEventoController;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\Cliente\CarritoController as ClienteCarritoController;
use App\Http\Controllers\Cliente\CompraController as ClienteCompraController;
use App\Http\Controllers\Cliente\PagoController as ClientePagoController;
use App\Http\Controllers\Cliente\PerfilController as ClientePerfilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Organizador\EventoController as OrganizadorEventoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

Route::get('/admin/ingresar', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('home');
    }

    return view('admin.auth.login');
})->name('admin.login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:cliente')->group(function () {
        Route::get('/mi-perfil', [ClientePerfilController::class, 'show'])->name('cliente.perfil');
        Route::patch('/mi-perfil', [ClientePerfilController::class, 'updateAvatar'])->name('cliente.perfil.update');
        Route::patch('/mi-perfil/datos', [ClientePerfilController::class, 'updateDatos'])->name('cliente.perfil.datos');
        Route::delete('/mi-perfil', [ClientePerfilController::class, 'destroy'])->name('cliente.perfil.destroy');
        Route::get('/mis-compras', [ClienteCompraController::class, 'index'])->name('cliente.compras.index');
        Route::get('/catalogo', [EventoController::class, 'index'])->name('cliente.eventos.index');

        Route::get('/reservas', fn () => redirect()->route('cliente.compras.index'))->name('reservas.index');
        Route::get('/carrito', [ClienteCarritoController::class, 'index'])->name('cliente.carrito.index');
        Route::post('/carrito/vaciar', [ClienteCarritoController::class, 'vaciar'])->name('cliente.carrito.vaciar');
        Route::post('/carrito/checkout', [ClienteCarritoController::class, 'checkout'])->name('cliente.carrito.checkout');
        Route::patch('/carrito/item/{item}', [ClienteCarritoController::class, 'actualizar'])->name('cliente.carrito.item.update');
        Route::delete('/carrito/item/{item}', [ClienteCarritoController::class, 'eliminar'])->name('cliente.carrito.item.destroy');

        Route::get('/eventos/{evento}/reservar', [ReservaController::class, 'create'])->name('reservas.create');
        Route::post('/eventos/{evento}/reservar', [ClienteCarritoController::class, 'agregar'])->name('reservas.store');

        Route::get('/pago/{reserva}', [ClientePagoController::class, 'show'])->name('cliente.pago.show');
        Route::post('/pago/{reserva}', [ClientePagoController::class, 'procesar'])->name('cliente.pago.procesar');
        Route::get('/pago/carrito/{carrito}', [ClientePagoController::class, 'showCarrito'])->name('cliente.pago.carrito');
        Route::post('/pago/carrito/{carrito}', [ClientePagoController::class, 'procesarCarrito'])->name('cliente.pago.carrito.procesar');
        Route::get('/compra-exitosa/{reserva}', [ClientePagoController::class, 'exito'])->name('cliente.compras.exito');
        Route::get('/compra-exitosa/carrito/{carrito}', [ClientePagoController::class, 'exitoCarrito'])->name('cliente.compras.exito-carrito');
        Route::get('/ticket/{reserva}', [ClientePagoController::class, 'ticket'])->name('cliente.ticket');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/eventos', [AdminEventoController::class, 'index'])->name('eventos.index');
        Route::post('/eventos', [AdminEventoController::class, 'store'])->name('eventos.store');
        Route::put('/eventos/{evento}', [AdminEventoController::class, 'update'])->name('eventos.update');
        Route::delete('/eventos/{evento}', [AdminEventoController::class, 'destroy'])->name('eventos.destroy');
        Route::get('/usuarios', [AdminUsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [AdminUsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [AdminUsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{usuario}/editar', [AdminUsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [AdminUsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [AdminUsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    Route::middleware('role:organizador')->prefix('organizador')->name('organizador.')->group(function () {
        Route::get('/eventos', [OrganizadorEventoController::class, 'index'])->name('eventos.index');
        Route::get('/eventos/crear', [OrganizadorEventoController::class, 'create'])->name('eventos.create');
        Route::post('/eventos', [OrganizadorEventoController::class, 'store'])->name('eventos.store');
    });

    Route::middleware('role:organizador')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
