<?php

use Inertia\Testing\AssertableInertia as Assert;

test('hanya admin yang mengelola pengguna', function () {
    $admin = akun('admin');

    $this->actingAs(akun('pemohon'))->get(route('pengguna.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('pengguna.index'))
        ->assertInertia(fn (Assert $page) => $page->component('Pengguna/Index'));

    $this->actingAs($admin)->post(route('pengguna.store'), [
        'name' => 'Petugas Baru',
        'email' => 'baru@contoh.id',
        'peran' => 'hse',
        'password' => 'rahasia123',
        'aktif' => true,
    ])->assertRedirect(route('pengguna.index'));

    $this->assertDatabaseHas('users', ['email' => 'baru@contoh.id', 'peran' => 'hse']);
});

test('admin tidak bisa mencabut perannya sendiri', function () {
    $admin = akun('admin');

    $this->actingAs($admin)->put(route('pengguna.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'peran' => 'pemohon',
        'aktif' => true,
    ])->assertSessionHasErrors('peran');

    expect($admin->fresh()->peran)->toBe('admin');
});
