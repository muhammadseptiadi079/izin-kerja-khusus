<?php

use App\Http\Controllers\AsistenAiController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\IzinKerjaController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TindakanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BerandaController::class, 'index'])->name('beranda');
Route::get('/jenis/{jenis}', [BerandaController::class, 'jenis'])->name('jenis.show');

Route::middleware('auth')->group(function () {
    Route::get('/dasbor', DasborController::class)->name('dasbor');
    Route::get('/tindakan', TindakanController::class)->name('tindakan');
    Route::inertia('/akun', 'Akun')->name('akun');

    // Monitoring: keadaan sekarang saat pekerjaan berjalan. Evaluasi: hasil per periode setelah pekerjaan.
    Route::get('/monitoring', MonitoringController::class)->name('monitoring');
    Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi');
    Route::get('/evaluasi/ekspor', [EvaluasiController::class, 'ekspor'])->name('evaluasi.ekspor');

    Route::get('/izin/{izin}/cetak', [IzinKerjaController::class, 'cetak'])->name('izin.cetak');
    Route::get('/izin/{izin}/dokumen/{jenis}', [IzinKerjaController::class, 'unduh'])->name('izin.dokumen');
    Route::post('/izin/{izin}/aksi', [IzinKerjaController::class, 'aksi'])->name('izin.aksi');
    Route::post('/izin/saran-ai', AsistenAiController::class)->middleware('throttle:20,1')->name('izin.saran-ai');
    Route::resource('izin', IzinKerjaController::class)->except('destroy')->parameters(['izin' => 'izin']);

    Route::resource('pengguna', PenggunaController::class)->except(['show', 'destroy'])->parameters(['pengguna' => 'pengguna']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
