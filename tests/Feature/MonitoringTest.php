<?php

use App\Exports\RekapIzinExport;
use App\Models\IzinKerja;
use Inertia\Testing\AssertableInertia as Assert;
use Maatwebsite\Excel\Facades\Excel;

function izinContoh(array $atribut = []): IzinKerja
{
    $izin = new IzinKerja(array_merge([
        'jenis' => 'ketinggian',
        'nik' => '10001',
        'nomor_wa' => '081200001111',
        'departemen' => 'Plant / Maintenance',
        'lokasi' => 'Pit 3',
        'uraian_pekerjaan' => 'Ganti idler',
        'pekerja' => 'Joko',
        'mulai_at' => now(),
        'selesai_at' => now()->addHours(4),
    ], $atribut));
    $izin->pemohon_id = akun()->id;
    $izin->status = $atribut['status'] ?? 'menunggu_hse';
    $izin->nomor = 'KT-'.now()->format('Ym').'-'.str_pad((string) (IzinKerja::count() + 1), 4, '0', STR_PAD_LEFT);
    $izin->diajukan_at = now()->subHours(2);
    $izin->save();

    return $izin;
}

test('monitoring merekap per jenis, lokasi, dan departemen', function () {
    izinContoh();
    izinContoh(['lokasi' => 'Disposal', 'departemen' => 'HSE']);
    izinContoh(['status' => 'draf']); // draf tidak dihitung

    $this->actingAs(akun('hse'))->get(route('monitoring'))
        ->assertInertia(fn (Assert $page) => $page->component('Monitoring')
            ->where('evaluasi.total', 2)
            ->where('perJenis.0', ['label' => 'Bekerja di Ketinggian', 'jumlah' => 2])
            ->has('perLokasi', 2)
            ->has('perDepartemen', 2));
});

test('pemohon hanya melihat rekap izinnya sendiri', function () {
    izinContoh();

    $this->actingAs(akun('pemohon'))->get(route('monitoring'))
        ->assertInertia(fn (Assert $page) => $page->where('evaluasi.total', 0));
});

test('rekap diekspor ke Excel', function () {
    Excel::fake();
    $izin = izinContoh();

    $this->actingAs(akun('hse'))->get(route('monitoring.ekspor'))->assertOk();

    Excel::assertDownloaded(
        'rekap-ikk-'.now()->startOfMonth()->format('Ymd').'-'.now()->endOfMonth()->format('Ymd').'.xlsx',
        fn (RekapIzinExport $ekspor) => $ekspor->collection()->contains('nomor', $izin->nomor)
            && $ekspor->map($ekspor->collection()->first())[5] === ' 081200001111'
    );
});

/** Izin selesai dengan evaluasi pasca pekerjaan. */
function izinDievaluasi(array $evaluasi): IzinKerja
{
    $izin = izinContoh(['status' => 'selesai']);
    $izin->forceFill([
        'disahkan_at' => $izin->mulai_at->copy()->subMinutes(30),
        'mulai_aktual_at' => $izin->mulai_at,
        'selesai_aktual_at' => $izin->selesai_at,
        'penutupan_diajukan_at' => $izin->selesai_at->copy()->addHour(),
        'ada_insiden' => false,
        ...$evaluasi,
    ])->save();

    return $izin;
}

test('monitoring merekap insiden dan kesesuaian waktu', function () {
    izinDievaluasi([]);
    izinDievaluasi(['ada_insiden' => true, 'kategori_insiden' => 'nyaris_celaka', 'uraian_insiden' => 'Alat jatuh', 'tindakan_insiden' => 'Diikat']);
    $lewat = izinDievaluasi([
        'selesai_aktual_at' => now()->addHours(4)->addMinutes(90),
        'penutupan_diajukan_at' => now()->addHours(4)->addMinutes(150),
    ]);
    izinContoh(); // belum dievaluasi, tidak ikut dihitung

    $this->actingAs(akun('hse'))->get(route('monitoring'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pasca.dievaluasi', 3)
            ->where('pasca.insiden', 1)
            ->where('pasca.tingkat_insiden', 33.3)
            ->where('pasca.sesuai_jadwal', 2)
            ->where('pasca.lewat_waktu', 1)
            ->where('pasca.rata_menit_lewat', 90)
            ->where('pasca.rata_jam_lapor', 1)
            ->where('perKategoriInsiden.0', ['label' => 'Nyaris celaka (near miss)', 'jumlah' => 1])
            ->has('daftarInsiden', 1)
            ->has('daftarWaktu', 1)
            ->where('daftarWaktu.0.nomor', $lewat->nomor)
            ->where('daftarWaktu.0.kesesuaian_waktu.kode', 'lewat_waktu'));
});

test('mulai sebelum izin disahkan ditandai sebagai pelanggaran paling berat', function () {
    $izin = izinDievaluasi([]);
    $izin->forceFill(['disahkan_at' => $izin->mulai_at->copy()->addHours(2), 'selesai_aktual_at' => $izin->selesai_at->copy()->addHours(2)])->save();

    expect($izin->fresh()->kesesuaianWaktu())
        ->kode->toBe('sebelum_disahkan')
        ->temuan->toHaveCount(2);
});

test('ekspor Excel menyertakan evaluasi pasca pekerjaan', function () {
    Excel::fake();
    izinDievaluasi(['ada_insiden' => true, 'kategori_insiden' => 'p3k', 'uraian_insiden' => 'Lecet', 'tindakan_insiden' => 'P3K']);

    $this->actingAs(akun('hse'))->get(route('monitoring.ekspor'))->assertOk();

    Excel::assertDownloaded(
        'rekap-ikk-'.now()->startOfMonth()->format('Ymd').'-'.now()->endOfMonth()->format('Ymd').'.xlsx',
        function (RekapIzinExport $ekspor) {
            $baris = $ekspor->map($ekspor->collection()->first());
            $kolom = array_combine($ekspor->headings(), $baris);

            return $kolom['Kesesuaian waktu'] === 'Sesuai jadwal'
                && $kolom['Insiden'] === 'Cedera ringan / P3K'
                && $kolom['Kronologi insiden'] === 'Lecet';
        }
    );
});
