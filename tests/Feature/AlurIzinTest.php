<?php

use App\Models\IzinKerja;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');

    $this->pemohon = akun('pemohon');
    $this->pengawas = akun('pengawas');
    $this->hse = akun('hse');
    $this->manajer = akun('manajer');
});

function dokumenLengkap(): array
{
    return collect(config('izin.dokumen'))
        ->map(fn ($_, $kunci) => UploadedFile::fake()->create($kunci.'.pdf', 200, 'application/pdf'))
        ->all();
}

function dataIzin(string $jenis = 'ketinggian', array $ubah = []): array
{
    $aturan = config('izin.jenis.'.$jenis);

    return array_merge([
        'jenis' => $jenis,
        'nik' => '10001',
        'nomor_wa' => '081200001111',
        'departemen' => 'Plant / Maintenance',
        'lokasi' => 'Pit 3',
        'lokasi_detail' => 'Conveyor CV-02',
        'dokumen' => dokumenLengkap(),
        'uraian_pekerjaan' => 'Ganti idler conveyor',
        'pekerja' => "Joko\nRudi",
        'mulai_at' => now()->addHour()->format('Y-m-d\TH:i'),
        'selesai_at' => now()->addHours(6)->format('Y-m-d\TH:i'),
        'bahaya' => [$aturan['bahaya'][0]],
        'pengendalian' => $aturan['pengendalian'],
        'apd' => ['Helm keselamatan', 'Full body harness'],
    ], $ubah);
}

function gasAman(): array
{
    return [
        'uji_gas' => ['o2' => 20.9, 'lel' => 0, 'h2s' => 0, 'co' => 0],
        'uji_gas_oleh' => 'Andi',
        'uji_gas_at' => now()->format('Y-m-d\TH:i'),
    ];
}

function ajukan(User $pemohon, array $data): IzinKerja
{
    test()->actingAs($pemohon)->post(route('izin.store'), [...$data, 'ajukan' => 1]);

    return IzinKerja::latest('id')->first();
}

function aksi(User $user, IzinKerja $izin, string $aksi, ?string $catatan = null)
{
    return test()->actingAs($user)->post(route('izin.aksi', $izin), ['aksi' => $aksi, 'catatan' => $catatan]);
}

test('izin lengkap melewati tiga tahap lalu ditutup', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    expect($izin->status)->toBe('menunggu_pengawas')
        ->and($izin->nomor)->toMatch('/^KT-\d{6}-0001$/');

    aksi($this->pengawas, $izin, 'setujui')->assertRedirect();
    expect($izin->fresh()->status)->toBe('menunggu_hse');

    aksi($this->hse, $izin, 'setujui');
    expect($izin->fresh()->status)->toBe('menunggu_manajer');

    aksi($this->manajer, $izin, 'setujui');
    expect($izin->fresh()->status)->toBe('aktif')
        ->and($izin->fresh()->disahkan_at)->not->toBeNull();

    aksi($this->pemohon, $izin, 'ajukan_penutupan', 'Area sudah dibersihkan');
    expect($izin->fresh()->status)->toBe('menunggu_penutupan');

    aksi($this->pengawas, $izin, 'tutup');
    expect($izin->fresh()->status)->toBe('selesai')
        ->and($izin->riwayat()->pluck('aksi')->all())
        ->toBe(['buat', 'ajukan', 'setujui', 'setujui', 'setujui', 'ajukan_penutupan', 'tutup']);
});

test('tahap hanya bisa diputuskan peran yang sesuai', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    aksi($this->hse, $izin, 'setujui')->assertForbidden();
    aksi($this->manajer, $izin, 'setujui')->assertForbidden();

    expect($izin->fresh()->status)->toBe('menunggu_pengawas');
});

test('pemohon tidak bisa menyetujui izinnya sendiri', function () {
    $pengawasPemohon = akun('pengawas');
    $izin = ajukan($pengawasPemohon, dataIzin());

    aksi($pengawasPemohon, $izin, 'setujui')->assertForbidden();
    aksi($this->pengawas, $izin, 'setujui')->assertRedirect();

    expect($izin->fresh()->status)->toBe('menunggu_hse');
});

test('pengendalian belum lengkap tetap draf dan semua masalah ditampilkan', function () {
    $data = dataIzin(ubah: ['pengendalian' => [config('izin.jenis.ketinggian.pengendalian')[0]]]);

    $respons = $this->actingAs($this->pemohon)->post(route('izin.store'), [...$data, 'ajukan' => 1]);
    $izin = IzinKerja::latest('id')->first();

    $respons->assertRedirect(route('izin.edit', $izin))->assertSessionHas('masalah');
    expect($izin->status)->toBe('draf')
        ->and($izin->nomor)->toBeNull()
        ->and(session('masalah'))->toHaveCount(4);

    $this->actingAs($this->pemohon)->get(route('izin.edit', $izin))
        ->assertInertia(fn (Assert $page) => $page->component('Izin/Form')->has('masalah', 4));
});

test('uji gas di luar batas menolak pengajuan', function () {
    $data = dataIzin('ruang_terbatas', [...gasAman(), 'uji_gas' => ['o2' => 18.0, 'lel' => 0, 'h2s' => 0, 'co' => 0]]);

    $izin = ajukan($this->pemohon, $data);
    expect($izin->status)->toBe('draf');

    $data['uji_gas']['o2'] = 20.9;
    $this->actingAs($this->pemohon)->put(route('izin.update', $izin), [...$data, 'ajukan' => 1]);

    expect($izin->fresh()->status)->toBe('menunggu_pengawas');
});

test('durasi melebihi batas jenis ditolak', function () {
    $izin = ajukan($this->pemohon, dataIzin('ruang_terbatas', [
        ...gasAman(),
        'selesai_at' => now()->addHours(12)->format('Y-m-d\TH:i'),
    ]));

    expect($izin->status)->toBe('draf');
});

test('penolakan wajib catatan dan pemohon bisa mengajukan ulang dengan nomor yang sama', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    aksi($this->pengawas, $izin, 'tolak')->assertSessionHasErrors('catatan');
    expect($izin->fresh()->status)->toBe('menunggu_pengawas');

    aksi($this->pengawas, $izin, 'tolak', 'Tambahkan nama fire watch');
    expect($izin->fresh()->status)->toBe('ditolak');

    $this->actingAs($this->pemohon)->get(route('izin.edit', $izin))
        ->assertInertia(fn (Assert $page) => $page->where('penolakan.catatan', 'Tambahkan nama fire watch'));

    $this->actingAs($this->pemohon)->put(route('izin.update', $izin), [...dataIzin(), 'ajukan' => 1]);

    $izin->refresh();
    expect($izin->status)->toBe('menunggu_pengawas')
        ->and($izin->nomor)->toEndWith('-0001');
});

test('izin yang sedang diproses tidak bisa diubah', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    $this->actingAs($this->pemohon)->get(route('izin.edit', $izin))->assertForbidden();
    $this->actingAs($this->pemohon)->put(route('izin.update', $izin), dataIzin())->assertForbidden();
});

test('HSE bisa menghentikan izin aktif, pemohon tidak', function () {
    $izin = ajukan($this->pemohon, dataIzin());
    foreach ([$this->pengawas, $this->hse, $this->manajer] as $penyetuju) {
        aksi($penyetuju, $izin, 'setujui');
    }

    aksi($this->pemohon, $izin, 'hentikan', 'Coba')->assertForbidden();
    aksi($this->hse, $izin, 'hentikan', 'Angin kencang di atas batas');

    expect($izin->fresh()->status)->toBe('dihentikan');
});

test('pemohon hanya melihat izinnya sendiri', function () {
    $izin = ajukan($this->pemohon, dataIzin());
    $orangLain = akun('pemohon');

    $this->actingAs($orangLain)->get(route('izin.show', $izin))->assertNotFound();
    $this->actingAs($orangLain)->get(route('izin.index'))
        ->assertInertia(fn (Assert $page) => $page->component('Izin/Index')->has('izin.data', 0));
    $this->actingAs($this->hse)->get(route('izin.show', $izin))
        ->assertInertia(fn (Assert $page) => $page->component('Izin/Show')->where('izin.nomor', $izin->nomor)->where('aksi', []));
});

test('dokumen wajib lengkap sebelum diajukan dan bisa dilengkapi belakangan', function () {
    $data = dataIzin();
    unset($data['dokumen']['jsea']);

    $izin = ajukan($this->pemohon, $data);
    expect($izin->status)->toBe('draf')->and($izin->dokumen)->toHaveCount(2);

    $this->actingAs($this->pemohon)->put(route('izin.update', $izin), [
        ...dataIzin(),
        'dokumen' => ['jsea' => UploadedFile::fake()->create('jsea.docx', 300)],
        'ajukan' => 1,
    ]);

    expect($izin->fresh()->status)->toBe('menunggu_pengawas')
        ->and($izin->fresh()->dokumen)->toHaveCount(3);
});

test('dokumen selain PDF atau Word ditolak', function () {
    $data = dataIzin();
    $data['dokumen']['sop'] = UploadedFile::fake()->create('sop.exe', 10);

    $this->actingAs($this->pemohon)->post(route('izin.store'), $data)->assertSessionHasErrors('dokumen.sop');

    expect(IzinKerja::count())->toBe(0);
});

test('dokumen hanya bisa diunduh yang berhak', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    $this->actingAs($this->hse)->get(route('izin.dokumen', [$izin, 'jsea']))->assertOk()->assertDownload('jsea.pdf');
    $this->actingAs(akun('pemohon'))->get(route('izin.dokumen', [$izin, 'jsea']))->assertNotFound();
});

test('izin dicetak sebagai PDF', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    $respons = $this->actingAs($this->pemohon)->get(route('izin.cetak', $izin))->assertOk();

    expect($respons->headers->get('content-type'))->toBe('application/pdf')
        ->and(substr($respons->getContent(), 0, 4))->toBe('%PDF');
});

test('dasbor pengawas menampilkan izin yang perlu tindakan', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    $this->actingAs($this->pengawas)->get(route('dasbor'))
        ->assertInertia(fn (Assert $page) => $page->component('Dasbor')
            ->has('perluTindakan', 1)
            ->where('perluTindakan.0.nomor', $izin->nomor));
});

test('halaman publik dan formulir bisa dibuka', function () {
    $this->get(route('beranda'))->assertInertia(fn (Assert $page) => $page->component('Beranda')
        ->has('jenis', 6)
        ->where('jenis.0.label_en', 'Working at Height')
        ->where('jenis.5.gambar', fn ($url) => str_contains($url, 'img/jenis/dekat_air.svg')));

    $this->get(route('jenis.show', 'ruang_terbatas'))
        ->assertInertia(fn (Assert $page) => $page->component('Jenis/Show')->where('jenis.uji_gas', true));
    $this->get('/jenis/tidak-ada')->assertNotFound();

    $this->actingAs($this->pemohon)->get(route('izin.create'))
        ->assertInertia(fn (Assert $page) => $page->component('Izin/PilihJenis'));
    $this->actingAs($this->pemohon)->get(route('izin.create', ['jenis' => 'kerja_panas']))
        ->assertInertia(fn (Assert $page) => $page->component('Izin/Form')
            ->where('aturan.uji_gas', true)
            ->where('izin.nik', $this->pemohon->nik));
});

test('pencarian daftar izin tidak peka huruf besar kecil', function () {
    $izin = ajukan($this->pemohon, dataIzin(ubah: ['lokasi_detail' => 'Conveyor CV-02']));

    $this->actingAs($this->hse)->get(route('izin.index', ['cari' => 'conveyor cv']))
        ->assertInertia(fn (Assert $page) => $page->has('izin.data', 1)->where('izin.data.0.nomor', $izin->nomor));
});

test('setiap jenis izin memakai animasi SVG', function () {
    $this->get(route('beranda'))->assertInertia(fn (Assert $page) => $page->where(
        'jenis',
        fn ($jenis) => collect($jenis)->every(fn ($j) => str_contains($j['gambar'], 'img/jenis/'.$j['kunci'].'.svg'))
    ));
});

test('angka tindakan mengikuti izin yang menunggu tiap orang', function () {
    $izin = ajukan($this->pemohon, dataIzin());
    $jumlah = fn ($user) => test()->actingAs($user)->get(route('dasbor'))->inertiaProps('jumlahTindakan');

    expect($jumlah($this->pengawas))->toBe(1)
        ->and($jumlah($this->hse))->toBe(0)
        ->and($jumlah($this->pemohon))->toBe(0);

    aksi($this->pengawas, $izin, 'tolak', 'Lengkapi nama fire watch');

    expect($jumlah($this->pengawas))->toBe(0)
        ->and($jumlah($this->pemohon))->toBe(1, 'Izin yang ditolak menunggu diperbaiki pemohon.');
});

test('izin aktif yang lewat waktu masuk tindakan pemohon', function () {
    $izin = ajukan($this->pemohon, dataIzin());
    foreach ([$this->pengawas, $this->hse, $this->manajer] as $penyetuju) {
        aksi($penyetuju, $izin, 'setujui');
    }

    $this->travel(7)->hours();

    $this->actingAs($this->pemohon)->get(route('tindakan'))
        ->assertInertia(fn (Assert $page) => $page->component('Tindakan')
            ->has('lewatWaktu', 1)
            ->where('jumlahTindakan', 1));
});

test('halaman tindakan menampilkan izin yang menunggu keputusan', function () {
    $izin = ajukan($this->pemohon, dataIzin());

    $this->actingAs($this->pengawas)->get(route('tindakan'))
        ->assertInertia(fn (Assert $page) => $page->component('Tindakan')
            ->has('menungguKeputusan', 1)
            ->where('menungguKeputusan.0.nomor', $izin->nomor)
            ->has('perluDiperbaiki', 0));
});

test('halaman akun bisa dibuka', function () {
    $this->actingAs($this->pemohon)->get(route('akun'))->assertInertia(fn (Assert $page) => $page->component('Akun'));
});

test('tamu tidak bisa membuka halaman akun dan tindakan', function () {
    $this->get(route('akun'))->assertRedirect(route('login'));
    $this->get(route('tindakan'))->assertRedirect(route('login'));
});
