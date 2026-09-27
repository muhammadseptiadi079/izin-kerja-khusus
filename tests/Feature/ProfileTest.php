<?php

use App\Models\User;

test('halaman profil bisa dibuka', function () {
    $this->actingAs(User::factory()->create())->get('/profile')->assertOk();
});

test('data diri bisa diperbarui', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'nik' => '30001',
            'nomor_wa' => '081299990000',
            'departemen' => 'HSE',
            'jabatan' => 'Safety Officer',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->nik)->toBe('30001')
        ->and($user->departemen)->toBe('HSE')
        ->and($user->email_verified_at)->toBeNull();
});

test('status verifikasi email tetap bila email tidak berubah', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
            'nik' => '30002',
            'nomor_wa' => '081299990001',
            'departemen' => 'Produksi',
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('akun tidak bisa dihapus sendiri agar riwayat izin tetap utuh', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertMethodNotAllowed();

    expect($user->fresh())->not->toBeNull();
});
