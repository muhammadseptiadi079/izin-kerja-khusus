<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->nullable()->unique();
            $table->string('jenis');
            $table->foreignId('pemohon_id')->constrained('users');
            $table->string('status')->default('draf')->index();
            $table->string('nik');
            $table->string('nomor_wa');
            $table->string('departemen');
            $table->string('lokasi');
            $table->string('lokasi_detail')->nullable();
            $table->text('uraian_pekerjaan');
            $table->text('peralatan')->nullable();
            $table->text('pekerja');
            $table->dateTime('mulai_at');
            $table->dateTime('selesai_at');
            $table->json('bahaya')->nullable();
            $table->text('bahaya_lain')->nullable();
            $table->json('pengendalian')->nullable();
            $table->text('pengendalian_tambahan')->nullable();
            $table->json('apd')->nullable();
            $table->json('uji_gas')->nullable();
            $table->string('uji_gas_oleh')->nullable();
            $table->dateTime('uji_gas_at')->nullable();
            $table->dateTime('diajukan_at')->nullable();
            $table->dateTime('disahkan_at')->nullable();
            $table->dateTime('ditutup_at')->nullable();
            $table->text('catatan_penutupan')->nullable();
            $table->timestamps();
        });

        Schema::create('dokumen_izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('izin_kerja_id')->constrained('izin_kerja')->cascadeOnDelete();
            $table->string('jenis');
            $table->string('nama_asli');
            $table->string('path');
            $table->unsignedInteger('ukuran');
            $table->timestamps();
            $table->unique(['izin_kerja_id', 'jenis']);
        });

        Schema::create('riwayat_izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('izin_kerja_id')->constrained('izin_kerja')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('aksi');
            $table->string('status_dari')->nullable();
            $table->string('status_ke');
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_izin');
        Schema::dropIfExists('dokumen_izin');
        Schema::dropIfExists('izin_kerja');
    }
};
