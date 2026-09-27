<?php

namespace Tests\Feature;

use App\Models\IzinKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlurIzinTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;
    private User $pengawas;
    private User $hse;
    private User $manajer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->pemohon = User::factory()->peran('pemohon')->create();
        $this->pengawas = User::factory()->peran('pengawas')->create();
        $this->hse = User::factory()->peran('hse')->create();
        $this->manajer = User::factory()->peran('manajer')->create();
    }

    private function dataIzin(string $jenis = 'ketinggian', array $ubah = []): array
    {
        $aturan = config('izin.jenis.'.$jenis);

        return array_merge([
            'jenis' => $jenis,
            'nik' => '10001',
            'nomor_wa' => '081200001111',
            'departemen' => 'Plant / Maintenance',
            'lokasi' => 'Pit 3',
            'lokasi_detail' => 'Conveyor CV-02',
            'dokumen' => $this->dokumenLengkap(),
            'uraian_pekerjaan' => 'Ganti idler conveyor',
            'pekerja' => "Joko\nRudi",
            'mulai_at' => now()->addHour()->format('Y-m-d\TH:i'),
            'selesai_at' => now()->addHours(6)->format('Y-m-d\TH:i'),
            'bahaya' => [$aturan['bahaya'][0]],
            'pengendalian' => $aturan['pengendalian'],
            'apd' => ['Helm keselamatan', 'Full body harness'],
        ], $ubah);
    }

    private function dokumenLengkap(): array
    {
        return collect(config('izin.dokumen'))
            ->map(fn ($_, $kunci) => UploadedFile::fake()->create($kunci.'.pdf', 200, 'application/pdf'))
            ->all();
    }

    private function ajukan(array $data): IzinKerja
    {
        $this->actingAs($this->pemohon)->post(route('izin.store'), [...$data, 'ajukan' => 1]);

        return IzinKerja::latest('id')->first();
    }

    private function aksi(User $user, IzinKerja $izin, string $aksi, ?string $catatan = null)
    {
        return $this->actingAs($user)->post(route('izin.aksi', $izin), ['aksi' => $aksi, 'catatan' => $catatan]);
    }

    public function test_izin_lengkap_melewati_tiga_tahap_lalu_ditutup(): void
    {
        $izin = $this->ajukan($this->dataIzin());

        $this->assertSame('menunggu_pengawas', $izin->status);
        $this->assertMatchesRegularExpression('/^KT-\d{6}-0001$/', $izin->nomor);

        $this->aksi($this->pengawas, $izin, 'setujui')->assertRedirect();
        $this->assertSame('menunggu_hse', $izin->fresh()->status);

        $this->aksi($this->hse, $izin, 'setujui');
        $this->assertSame('menunggu_manajer', $izin->fresh()->status);

        $this->aksi($this->manajer, $izin, 'setujui');
        $this->assertSame('aktif', $izin->fresh()->status);
        $this->assertNotNull($izin->fresh()->disahkan_at);

        $this->aksi($this->pemohon, $izin, 'ajukan_penutupan', 'Area sudah dibersihkan');
        $this->assertSame('menunggu_penutupan', $izin->fresh()->status);

        $this->aksi($this->pengawas, $izin, 'tutup');
        $this->assertSame('selesai', $izin->fresh()->status);

        $this->assertSame(
            ['buat', 'ajukan', 'setujui', 'setujui', 'setujui', 'ajukan_penutupan', 'tutup'],
            $izin->riwayat()->pluck('aksi')->all()
        );
    }

    public function test_tahap_hanya_bisa_diputuskan_peran_yang_sesuai(): void
    {
        $izin = $this->ajukan($this->dataIzin());

        $this->aksi($this->hse, $izin, 'setujui')->assertForbidden();
        $this->aksi($this->manajer, $izin, 'setujui')->assertForbidden();
        $this->assertSame('menunggu_pengawas', $izin->fresh()->status);
    }

    public function test_pemohon_tidak_bisa_menyetujui_izinnya_sendiri(): void
    {
        $pengawasPemohon = User::factory()->peran('pengawas')->create();
        $this->actingAs($pengawasPemohon)->post(route('izin.store'), [...$this->dataIzin(), 'ajukan' => 1]);
        $izin = IzinKerja::latest('id')->first();

        $this->aksi($pengawasPemohon, $izin, 'setujui')->assertForbidden();
        $this->aksi($this->pengawas, $izin, 'setujui')->assertRedirect();
        $this->assertSame('menunggu_hse', $izin->fresh()->status);
    }

    public function test_pengendalian_belum_lengkap_tetap_draf(): void
    {
        $data = $this->dataIzin(ubah: ['pengendalian' => [config('izin.jenis.ketinggian.pengendalian')[0]]]);

        $respons = $this->actingAs($this->pemohon)->post(route('izin.store'), [...$data, 'ajukan' => 1]);
        $izin = IzinKerja::latest('id')->first();

        $respons->assertRedirect(route('izin.edit', $izin));
        $respons->assertSessionHasErrors('ajukan');
        $this->assertSame('draf', $izin->status);
        $this->assertNull($izin->nomor);
    }

    public function test_uji_gas_di_luar_batas_menolak_pengajuan(): void
    {
        $data = $this->dataIzin('ruang_terbatas', [
            'uji_gas' => ['o2' => 18.0, 'lel' => 0, 'h2s' => 0, 'co' => 0],
            'uji_gas_oleh' => 'Andi',
            'uji_gas_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $izin = $this->ajukan($data);
        $this->assertSame('draf', $izin->status);

        $data['uji_gas']['o2'] = 20.9;
        $this->actingAs($this->pemohon)->put(route('izin.update', $izin), [...$data, 'ajukan' => 1]);
        $this->assertSame('menunggu_pengawas', $izin->fresh()->status);
    }

    public function test_durasi_melebihi_batas_jenis_ditolak(): void
    {
        $izin = $this->ajukan($this->dataIzin('ruang_terbatas', [
            'selesai_at' => now()->addHours(12)->format('Y-m-d\TH:i'),
            'uji_gas' => ['o2' => 20.9, 'lel' => 0, 'h2s' => 0, 'co' => 0],
            'uji_gas_oleh' => 'Andi',
            'uji_gas_at' => now()->format('Y-m-d\TH:i'),
        ]));

        $this->assertSame('draf', $izin->status);
    }

    public function test_penolakan_wajib_catatan_dan_pemohon_bisa_mengajukan_ulang(): void
    {
        $izin = $this->ajukan($this->dataIzin());

        $this->aksi($this->pengawas, $izin, 'tolak')->assertSessionHasErrors('catatan');
        $this->assertSame('menunggu_pengawas', $izin->fresh()->status);

        $this->aksi($this->pengawas, $izin, 'tolak', 'Tambahkan nama fire watch');
        $this->assertSame('ditolak', $izin->fresh()->status);

        $this->actingAs($this->pemohon)->put(route('izin.update', $izin), [...$this->dataIzin(), 'ajukan' => 1]);
        $izin->refresh();
        $this->assertSame('menunggu_pengawas', $izin->status);
        $this->assertMatchesRegularExpression('/-0001$/', $izin->nomor, 'Nomor tetap sama saat diajukan ulang.');
    }

    public function test_izin_yang_sedang_diproses_tidak_bisa_diubah(): void
    {
        $izin = $this->ajukan($this->dataIzin());

        $this->actingAs($this->pemohon)->get(route('izin.edit', $izin))->assertForbidden();
        $this->actingAs($this->pemohon)->put(route('izin.update', $izin), $this->dataIzin())->assertForbidden();
    }

    public function test_hse_bisa_menghentikan_izin_aktif(): void
    {
        $izin = $this->ajukan($this->dataIzin());
        foreach ([$this->pengawas, $this->hse, $this->manajer] as $penyetuju) {
            $this->aksi($penyetuju, $izin, 'setujui');
        }

        $this->aksi($this->pemohon, $izin, 'hentikan', 'Coba')->assertForbidden();
        $this->aksi($this->hse, $izin, 'hentikan', 'Angin kencang di atas batas');
        $this->assertSame('dihentikan', $izin->fresh()->status);
    }

    public function test_pemohon_hanya_melihat_izinnya_sendiri(): void
    {
        $izin = $this->ajukan($this->dataIzin());
        $orangLain = User::factory()->peran('pemohon')->create();

        $this->actingAs($orangLain)->get(route('izin.show', $izin))->assertNotFound();
        $this->actingAs($orangLain)->get(route('izin.index'))->assertDontSee($izin->nomor);
        $this->actingAs($this->hse)->get(route('izin.show', $izin))->assertOk()->assertSee($izin->nomor);
    }

    public function test_dokumen_wajib_lengkap_sebelum_diajukan(): void
    {
        $data = $this->dataIzin();
        unset($data['dokumen']['jsea']);

        $izin = $this->ajukan($data);
        $this->assertSame('draf', $izin->status);
        $this->assertCount(2, $izin->dokumen);

        // Mengunggah dokumen yang kurang saja sudah cukup; dokumen lama tetap tersimpan.
        $lengkapi = [...$this->dataIzin(), 'dokumen' => ['jsea' => UploadedFile::fake()->create('jsea.docx', 300)], 'ajukan' => 1];
        $this->actingAs($this->pemohon)->put(route('izin.update', $izin), $lengkapi);

        $this->assertSame('menunggu_pengawas', $izin->fresh()->status);
        $this->assertCount(3, $izin->fresh()->dokumen);
    }

    public function test_dokumen_selain_pdf_atau_word_ditolak(): void
    {
        $data = $this->dataIzin();
        $data['dokumen']['sop'] = UploadedFile::fake()->create('sop.exe', 10);

        $this->actingAs($this->pemohon)->post(route('izin.store'), $data)->assertSessionHasErrors('dokumen.sop');
        $this->assertSame(0, IzinKerja::count());
    }

    public function test_dokumen_hanya_bisa_diunduh_yang_berhak(): void
    {
        $izin = $this->ajukan($this->dataIzin());
        $orangLain = User::factory()->peran('pemohon')->create();

        $this->actingAs($this->hse)->get(route('izin.dokumen', [$izin, 'jsea']))->assertOk()->assertDownload('jsea.pdf');
        $this->actingAs($orangLain)->get(route('izin.dokumen', [$izin, 'jsea']))->assertNotFound();
    }

    public function test_monitoring_merekap_dan_mengekspor(): void
    {
        $izin = $this->ajukan($this->dataIzin());
        $this->aksi($this->pengawas, $izin, 'setujui');

        $this->actingAs($this->hse)->get(route('monitoring'))
            ->assertOk()->assertSee('Bekerja di Ketinggian')->assertSee('Pit 3')->assertSee('Plant / Maintenance');

        $csv = $this->actingAs($this->hse)->get(route('monitoring.ekspor'))->assertOk()->streamedContent();
        $this->assertStringContainsString($izin->nomor, $csv);
        $this->assertStringContainsString('081200001111', $csv);
    }

    public function test_halaman_utama_bisa_dibuka(): void
    {
        $izin = $this->ajukan($this->dataIzin());

        $this->actingAs($this->pengawas)->get(route('dasbor'))->assertOk()->assertSee('Perlu tindakan Anda')->assertSee($izin->nomor);
        $this->get(route('beranda'))->assertOk()->assertSee('Penebangan Pohon')->assertSee('Working Near Water');
        $this->actingAs($this->pemohon)->get(route('izin.create'))->assertOk()->assertSee('Pengelasan di Luar Workshop');
        $this->actingAs($this->pemohon)->get(route('izin.create', ['jenis' => 'kerja_panas']))->assertOk()->assertSee('Uji gas')->assertSee('JSEA');
        $this->actingAs($this->pemohon)->get(route('izin.index'))->assertOk();
        $this->actingAs($this->pemohon)->get(route('izin.cetak', $izin))->assertOk()->assertSee($izin->nomor);
    }
}
