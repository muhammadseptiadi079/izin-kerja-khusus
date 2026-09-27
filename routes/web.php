<?php

use App\Http\Controllers\Auth\DaftarController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\IzinKerjaController;
use App\Http\Controllers\JenisIzinController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PenggunaController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'beranda')->name('beranda');
Route::get('/jenis/{jenis}', [JenisIzinController::class, 'show'])->name('jenis.show');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/daftar', [DaftarController::class, 'create'])->name('daftar');
    Route::post('/daftar', [DaftarController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dasbor', DasborController::class)->name('dasbor');

    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
    Route::get('/monitoring/ekspor', [MonitoringController::class, 'ekspor'])->name('monitoring.ekspor');

    Route::get('/izin/{izin}/cetak', [IzinKerjaController::class, 'cetak'])->name('izin.cetak');
    Route::get('/izin/{izin}/dokumen/{jenis}', [IzinKerjaController::class, 'unduh'])->name('izin.dokumen');
    Route::post('/izin/{izin}/aksi', [IzinKerjaController::class, 'aksi'])->name('izin.aksi');
    Route::resource('izin', IzinKerjaController::class)->except('destroy')->parameters(['izin' => 'izin']);

    Route::resource('pengguna', PenggunaController::class)->except(['show', 'destroy'])->parameters(['pengguna' => 'pengguna']);
});
