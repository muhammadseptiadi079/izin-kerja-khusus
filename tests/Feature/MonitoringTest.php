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
