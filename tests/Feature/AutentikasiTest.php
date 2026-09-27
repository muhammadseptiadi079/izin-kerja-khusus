<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_pengguna_bisa_masuk(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dasbor'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $user = User::factory()->create(['aktif' => false]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_hanya_admin_yang_mengelola_pengguna(): void
    {
        $admin = User::factory()->peran('admin')->create();
        $pemohon = User::factory()->create();

        $this->actingAs($pemohon)->get(route('pengguna.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('pengguna.index'))->assertOk();

        $this->actingAs($admin)->post(route('pengguna.store'), [
            'name' => 'Petugas Baru',
            'email' => 'baru@contoh.id',
            'peran' => 'hse',
            'password' => 'rahasia123',
            'aktif' => 1,
        ])->assertRedirect(route('pengguna.index'));

        $this->assertDatabaseHas('users', ['email' => 'baru@contoh.id', 'peran' => 'hse']);
    }

    public function test_admin_tidak_bisa_mencabut_perannya_sendiri(): void
    {
        $admin = User::factory()->peran('admin')->create();

        $this->actingAs($admin)->put(route('pengguna.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'peran' => 'pemohon',
            'aktif' => 1,
        ])->assertSessionHasErrors('peran');

        $this->assertSame('admin', $admin->fresh()->peran);
    }
}
