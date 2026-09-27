<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Akun contoh untuk setiap peran. Kata sandi semua akun: "password".
     * Ganti atau nonaktifkan akun-akun ini sebelum dipakai sungguhan.
     */
    public function run(): void
    {
        $akun = [
            ['Admin Sistem', 'admin@contoh.id', 'admin', 'Administrator', 'HRGA', 'ADM001'],
            ['Budi Pemohon', 'pemohon@contoh.id', 'pemohon', 'Supervisor Maintenance', 'Plant / Maintenance', '10001'],
            ['Sari Pengawas', 'pengawas@contoh.id', 'pengawas', 'Pengawas Area', 'Produksi', '10002'],
            ['Andi HSE', 'hse@contoh.id', 'hse', 'HSE Officer', 'HSE', '10003'],
            ['Rina Manajer', 'manajer@contoh.id', 'manajer', 'Kepala Teknik Tambang', 'Produksi', '10004'],
        ];

        foreach ($akun as [$nama, $email, $peran, $jabatan, $departemen, $nik]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $nama,
                'password' => 'password',
                'peran' => $peran,
                'jabatan' => $jabatan,
                'departemen' => $departemen,
                'nik' => $nik,
                'nomor_wa' => '0812000'.substr($nik, -4),
                'aktif' => true,
            ]);
        }
    }
}
