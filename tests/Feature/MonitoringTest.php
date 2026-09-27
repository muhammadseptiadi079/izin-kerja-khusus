<?php

use App\Models\IzinKerja;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/** Izin dengan status dan jadwal tertentu, relatif terhadap sekarang (menit). */
function izinPantau(string $status, int $mulai, int $selesai, ?User $pemohon = null, array $atribut = []): IzinKerja
{
    $izin = new IzinKerja([
        'jenis' => 'ketinggian',
        'nik' => '10001',
        'nomor_wa' => '081200001111',
        'departemen' => 'Produksi',
        'lokasi' => 'Pit 3',
        'uraian_pekerjaan' => 'Pasang atap',
        'pekerja' => "Joko\nRudi\n\nAgus",
        'mulai_at' => now()->addMinutes($mulai),
        'selesai_at' => now()->addMinutes($selesai),
    ]);
    $izin->forceFill([
        'pemohon_id' => ($pemohon ?? akun())->id,
        'status' => $status,
        'nomor' => 'KT-'.now()->format('Ym').'-'.str_pad((string) (IzinKerja::count() + 1), 4, '0', STR_PAD_LEFT),
        'diajukan_at' => now()->subHour(),
        'disahkan_at' => $status === 'aktif' ? now()->subMinutes(30) : null,
        ...$atribut,
    ])->save();

    return $izin;
}

test('monitoring membedakan pekerjaan berjalan dan yang baru terjadwal', function () {
    izinPantau('aktif', -60, 180);                 // berjalan normal
    izinPantau('aktif', -120, 30);                 // hampir habis
    $lewat = izinPantau('aktif', -300, -20);       // lewat waktu
    izinPantau('aktif', 120, 480);                 // disahkan, belum mulai
    izinPantau('selesai', -600, -300);             // tidak ikut

    $this->actingAs(akun('hse'))->get(route('monitoring'))
        ->assertInertia(fn (Assert $page) => $page->component('Monitoring')
            ->where('ringkasan.berjalan', 3)
            ->where('ringkasan.pekerja', 9) // 3 orang per izin, baris kosong diabaikan
            ->where('ringkasan.hampir_habis', 1)
            ->where('ringkasan.lewat_waktu', 1)
            ->where('ringkasan.terjadwal_24_jam', 1)
            ->where('berjalan.0.nomor', $lewat->nomor)
            ->where('berjalan.0.keadaan', 'lewat_waktu')
            ->has('jadwal', 1)
            ->where('perLokasi.0', ['label' => 'Pit 3', 'jumlah' => 3, 'pekerja' => 9]));
});

test('antrian persetujuan dikelompokkan per tahap dan menandai yang mendesak', function () {
    $mendesak = izinPantau('menunggu_hse', 60, 300);
    izinPantau('menunggu_hse', 600, 900);
    izinPantau('menunggu_pengawas', 1000, 1300);
    izinPantau('menunggu_penutupan', -300, -10, atribut: ['ada_insiden' => false, 'penutupan_diajukan_at' => now()->subMinutes(5)]);

    $this->actingAs(akun('manajer'))->get(route('monitoring'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('ringkasan.menunggu_persetujuan', 3)
            ->where('ringkasan.menunggu_penutupan', 1)
            ->where('antrian.0.status', 'menunggu_pengawas')
            ->has('antrian.1.izin', 2)
            ->where('antrian.1.izin', fn ($izin) => collect($izin)->firstWhere('nomor', $mendesak->nomor)['mendesak'] === true)
            ->where('penutupan.0.label_insiden', 'Tidak ada insiden'));
});

test('pemohon hanya memantau izinnya sendiri', function () {
    $saya = akun('pemohon');
    izinPantau('aktif', -60, 120, $saya);
    izinPantau('aktif', -60, 120);

    $this->actingAs($saya)->get(route('monitoring'))
        ->assertInertia(fn (Assert $page) => $page->where('ringkasan.berjalan', 1));
});

test('tamu tidak bisa membuka monitoring dan evaluasi', function () {
    $this->get(route('monitoring'))->assertRedirect(route('login'));
    $this->get(route('evaluasi'))->assertRedirect(route('login'));
});
