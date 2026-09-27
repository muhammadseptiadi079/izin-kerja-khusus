<?php

use App\Models\User;

test('halaman daftar bisa dibuka', function () {
    $this->get('/register')->assertOk();
});

test('pekerja bisa mendaftar sebagai pemohon', function () {
    $response = $this->post('/register', [
        'name' => 'Joko Susilo',
        'nik' => '20001',
        'nomor_wa' => '081234567890',
        'departemen' => 'Produksi',
        'email' => 'joko@contoh.id',
        'password' => 'password',
        'password_confirmation' => 'password',
        'peran' => 'manajer',
    ]);

    $user = User::firstWhere('email', 'joko@contoh.id');

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('izin.create', absolute: false));
    expect($user->peran)->toBe('pemohon', 'Pendaftaran mandiri tidak boleh memilih peran penyetuju.')
        ->and($user->nik)->toBe('20001');
});

test('NIK dan nomor WA wajib diisi saat mendaftar', function () {
    $this->post('/register', [
        'name' => 'Tanpa NIK',
        'email' => 'tanpa@contoh.id',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['nik', 'nomor_wa', 'departemen']);

    $this->assertGuest();
});
