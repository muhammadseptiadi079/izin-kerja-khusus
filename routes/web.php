<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\IzinKerjaController;
use App\Http\Controllers\PenggunaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DasborController::class)->name('dasbor');

    Route::get('/izin/{izin}/cetak', [IzinKerjaController::class, 'cetak'])->name('izin.cetak');
    Route::post('/izin/{izin}/aksi', [IzinKerjaController::class, 'aksi'])->name('izin.aksi');
    Route::resource('izin', IzinKerjaController::class)->except('destroy')->parameters(['izin' => 'izin']);

    Route::resource('pengguna', PenggunaController::class)->except(['show', 'destroy'])->parameters(['pengguna' => 'pengguna']);
});
