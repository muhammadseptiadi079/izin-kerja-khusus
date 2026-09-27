<?php

namespace App\Http\Controllers;

use App\Exports\RekapIzinExport;
use App\Models\IzinKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvaluasiController extends Controller
{
    public function index(Request $request): Response
    {
        [$dari, $sampai] = $this->periode($request);
        $izin = $this->query($request, $dari, $sampai)->get();

        $rekap = fn (string $kolom, callable $label) => $izin->groupBy($kolom)
            ->map(fn ($grup, $kunci) => ['label' => $label($kunci), 'jumlah' => $grup->count()])
            ->sortByDesc('jumlah')->values();

        $diajukan = $izin->whereNotNull('diajukan_at');
        $disahkan = $izin->whereNotNull('disahkan_at');

        // Evaluasi pasca pekerjaan: hanya izin yang sudah dilaporkan (penutupan diajukan, selesai, atau dihentikan).
        $dievaluasi = $izin->whereNotNull('ada_insiden');
        $berinsiden = $dievaluasi->where('ada_insiden', true);
        $waktu = $izin->map(fn (IzinKerja $i) => [$i, $i->kesesuaianWaktu()])->filter(fn ($p) => $p[1] !== null);
        $labelWaktu = ['sesuai' => 'Sesuai jadwal', 'mulai_awal' => 'Mulai sebelum jadwal', 'lewat_waktu' => 'Selesai lewat waktu', 'sebelum_disahkan' => 'Mulai sebelum disahkan'];
        $lewat = $waktu->filter(fn ($p) => $p[1]['lewat_menit'] > config('izin.toleransi_waktu_menit'));
        $jedaLapor = $izin->filter(fn ($i) => $i->penutupan_diajukan_at && $i->selesai_aktual_at);

        $baris = fn (IzinKerja $i, array $tambahan) => [...$i->ringkas(), ...$tambahan];

        return Inertia::render('Evaluasi', [
            'filter' => [
                'dari' => $dari->format('Y-m-d'),
                'sampai' => $sampai->format('Y-m-d'),
                ...$request->only('jenis', 'lokasi', 'departemen'),
            ],
            'evaluasi' => [
                'total' => $izin->count(),
                'diajukan' => $diajukan->count(),
                'disetujui' => $disahkan->count(),
                'ditolak_sekali' => $izin->filter(fn ($i) => $i->riwayat->contains('aksi', 'tolak'))->count(),
                'dihentikan' => $izin->where('status', 'dihentikan')->count(),
                'selesai' => $izin->where('status', 'selesai')->count(),
                'lewat_waktu' => $izin->filter->lewatWaktu()->count(),
                // Rata-rata jam dari diajukan sampai disahkan, sebagai ukuran kecepatan persetujuan.
                'rata_jam_persetujuan' => $disahkan->isEmpty() ? null
                    : round($disahkan->avg(fn ($i) => $i->diajukan_at->diffInMinutes($i->disahkan_at)) / 60, 1),
            ],
            'pasca' => [
                'dievaluasi' => $dievaluasi->count(),
                'insiden' => $berinsiden->count(),
                'tingkat_insiden' => $dievaluasi->isEmpty() ? null : round($berinsiden->count() / $dievaluasi->count() * 100, 1),
                'dilaporkan_waktu' => $waktu->count(),
                'sesuai_jadwal' => $waktu->filter(fn ($p) => $p[1]['kode'] === 'sesuai')->count(),
                'lewat_waktu' => $lewat->count(),
                'sebelum_disahkan' => $waktu->filter(fn ($p) => $p[1]['kode'] === 'sebelum_disahkan')->count(),
                'rata_menit_lewat' => $lewat->isEmpty() ? null : (int) round($lewat->avg(fn ($p) => $p[1]['lewat_menit'])),
                // Jeda dari pekerjaan selesai sampai penutupan dilaporkan.
                'rata_jam_lapor' => $jedaLapor->isEmpty() ? null
                    : round($jedaLapor->avg(fn ($i) => max(0, $i->selesai_aktual_at->diffInMinutes($i->penutupan_diajukan_at))) / 60, 1),
            ],
            'perKategoriInsiden' => $berinsiden->groupBy('kategori_insiden')
                ->map(fn ($grup, $k) => ['label' => config('izin.insiden.'.$k, $k), 'jumlah' => $grup->count()])
                ->sortByDesc('jumlah')->values(),
            'perKesesuaian' => $waktu->groupBy(fn ($p) => $p[1]['kode'])
                ->map(fn ($grup, $k) => ['label' => $labelWaktu[$k], 'jumlah' => $grup->count()])
                ->sortByDesc('jumlah')->values(),
            'daftarInsiden' => $berinsiden->sortByDesc('selesai_aktual_at')->take(20)
                ->map(fn ($i) => $baris($i, ['label_insiden' => $i->labelInsiden(), 'uraian_insiden' => $i->uraian_insiden]))->values(),
            'daftarWaktu' => $waktu->filter(fn ($p) => in_array($p[1]['kode'], ['lewat_waktu', 'sebelum_disahkan', 'mulai_awal'], true))
                ->sortByDesc(fn ($p) => $p[1]['kode'] === 'sebelum_disahkan' ? PHP_INT_MAX : $p[1]['lewat_menit'])->take(20)
                ->map(fn ($p) => $baris($p[0], ['kesesuaian_waktu' => $p[1]]))->values(),
            'perJenis' => $rekap('jenis', fn ($k) => config('izin.jenis.'.$k.'.label', $k)),
            'perLokasi' => $rekap('lokasi', fn ($k) => $k),
            'perDepartemen' => $rekap('departemen', fn ($k) => $k),
            'perStatus' => $rekap('status', fn ($k) => IzinKerja::STATUS[$k] ?? $k),
            'perHari' => $izin->groupBy(fn ($i) => $i->mulai_at->format('Y-m-d'))
                ->map(fn ($grup, $hari) => ['tanggal' => $hari, 'jumlah' => $grup->count()])
                ->sortKeys()->values(),
            'pilihan' => [
                'jenis' => collect(config('izin.jenis'))->map(fn ($j) => $j['label']),
                'lokasi' => config('izin.lokasi'),
                'departemen' => config('izin.departemen'),
            ],
        ]);
    }

    public function ekspor(Request $request): BinaryFileResponse
    {
        [$dari, $sampai] = $this->periode($request);
        $izin = $this->query($request, $dari, $sampai)->orderBy('mulai_at')->get();

        return Excel::download(
            new RekapIzinExport($izin),
            'rekap-ikk-'.$dari->format('Ymd').'-'.$sampai->format('Ymd').'.xlsx'
        );
    }

    private function periode(Request $request): array
    {
        $request->validate(['dari' => ['nullable', 'date'], 'sampai' => ['nullable', 'date']]);

        $dari = $request->filled('dari') ? Carbon::parse($request->dari)->startOfDay() : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay() : now()->endOfMonth();

        return $sampai->lt($dari) ? [$sampai->copy()->startOfDay(), $dari->copy()->endOfDay()] : [$dari, $sampai];
    }

    /** Izin yang jadwal mulainya jatuh di periode, tanpa draf dan yang dibatalkan. */
    private function query(Request $request, Carbon $dari, Carbon $sampai)
    {
        return IzinKerja::terlihatOleh($request->user())
            ->with('pemohon', 'riwayat')
            ->whereNotIn('status', ['draf', 'dibatalkan'])
            ->whereBetween('mulai_at', [$dari, $sampai])
            ->when($request->jenis, fn ($q, $jenis) => $q->where('jenis', $jenis))
            ->when($request->lokasi, fn ($q, $lokasi) => $q->where('lokasi', $lokasi))
            ->when($request->departemen, fn ($q, $d) => $q->where('departemen', $d));
    }
}
