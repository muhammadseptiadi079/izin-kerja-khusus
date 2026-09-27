<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Evaluasi pasca pekerjaan: jam kerja sebenarnya (untuk kesesuaian dengan
 * jadwal yang diajukan), ada tidaknya insiden, dan pemeriksaan area saat penutupan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_kerja', function (Blueprint $table) {
            $table->dateTime('mulai_aktual_at')->nullable()->after('selesai_at');
            $table->dateTime('selesai_aktual_at')->nullable()->after('mulai_aktual_at');
            $table->dateTime('penutupan_diajukan_at')->nullable()->after('disahkan_at');
            $table->boolean('ada_insiden')->nullable()->after('catatan_penutupan');
            $table->string('kategori_insiden')->nullable()->after('ada_insiden');
            $table->text('uraian_insiden')->nullable()->after('kategori_insiden');
            $table->text('tindakan_insiden')->nullable()->after('uraian_insiden');
            $table->json('pemeriksaan_penutupan')->nullable()->after('tindakan_insiden');
        });
    }

    public function down(): void
    {
        Schema::table('izin_kerja', function (Blueprint $table) {
            $table->dropColumn([
                'mulai_aktual_at', 'selesai_aktual_at', 'penutupan_diajukan_at', 'ada_insiden',
                'kategori_insiden', 'uraian_insiden', 'tindakan_insiden', 'pemeriksaan_penutupan',
            ]);
        });
    }
};
