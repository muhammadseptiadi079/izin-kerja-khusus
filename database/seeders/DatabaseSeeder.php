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
            ['Admin Sistem', 'admin@contoh.id', 'admin', 'Administrator', 'IT'],
            ['Budi Pemohon', 'pemohon@contoh.id', 'pemohon', 'Supervisor Maintenance', 'Maintenance'],
            ['Sari Pengawas', 'pengawas@contoh.id', 'pengawas', 'Pengawas Area Plant', 'Produksi'],
            ['Andi HSE', 'hse@contoh.id', 'hse', 'HSE Officer', 'HSE'],
            ['Rina Manajer', 'manajer@contoh.id', 'manajer', 'Plant Manager', 'Produksi'],
        ];

        foreach ($akun as [$nama, $email, $peran, $jabatan, $departemen]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $nama,
                'password' => 'password',
                'peran' => $peran,
                'jabatan' => $jabatan,
                'departemen' => $departemen,
                'aktif' => true,
            ]);
        }
    }
}
