<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ReservacionController;
use Illuminate\Support\Facades\Route;

// Landing page - main view with reservation modal + active reservations
Route::get('/', [LandingController::class, 'index'])->name('home');

// Auth
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Reservas (protected)
Route::middleware('auth.check')->group(function () {
    Route::get('/reservas/listado', [ReservacionController::class, 'listado'])->name('reservas.listado');
    Route::get('/reservas/slots', [ReservacionController::class, 'slots'])->name('reservas.slots');
    Route::post('/reservas', [ReservacionController::class, 'store'])->name('reservas.store');
    Route::delete('/reservas/{reserva}', [ReservacionController::class, 'cancel'])->name('reservas.cancel');
    Route::get('/reservas/{reserva}/receipt', [ReservacionController::class, 'downloadPdf'])->name('reservas.receipt');
});
