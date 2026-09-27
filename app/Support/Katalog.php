<?php

namespace App\Support;

use App\Models\IzinKerja;
use App\Models\User;

/**
 * Aturan izin dari config/izin.php dalam bentuk yang dipakai halaman Vue.
 */
class Katalog
{
    public static function jenis(): array
    {
        return collect(config('izin.jenis'))
            ->map(fn (array $j, string $kunci) => [
                'kunci' => $kunci,
                'label' => $j['label'],
                'label_en' => $j['label_en'],
                'kode' => $j['kode'],
                'deskripsi' => $j['deskripsi'],
                'penjelasan' => $j['penjelasan'],
                'durasi_maks_jam' => $j['durasi_maks_jam'],
                'uji_gas' => $j['uji_gas'],
                'bahaya' => $j['bahaya'],
                'pengendalian' => $j['pengendalian'],
                'gambar' => GambarJenis::url($kunci),
            ])
            ->values()
            ->all();
    }

    public static function umum(): array
    {
        return [
            'lokasi' => config('izin.lokasi'),
            'departemen' => config('izin.departemen'),
            'dokumen' => config('izin.dokumen'),
            'dokumen_maks_kb' => config('izin.dokumen_maks_kb'),
            'uji_gas' => config('izin.uji_gas'),
            'apd' => config('izin.apd'),
            'tahap' => collect(config('izin.tahap_persetujuan'))
                ->map(fn ($t, $status) => ['status' => $status, 'label' => $t['label'], 'peran' => $t['peran']])
                ->values()->all(),
            'status' => IzinKerja::STATUS,
            'peran' => User::PERAN,
        ];
    }
}
