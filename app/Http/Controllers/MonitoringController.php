<?php

namespace App\Http\Controllers;

use App\Models\IzinKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Monitoring: keadaan izin SEKARANG, saat pekerjaan berjalan.
 * Rekap per periode dan evaluasi setelah pekerjaan ada di EvaluasiController.
 */
class MonitoringController extends Controller
{
    /** Izin yang berakhir dalam rentang ini dianggap hampir habis. */
    private const HAMPIR_HABIS_MENIT = 60;

    public function __invoke(Request $request): Response
    {
        $sekarang = now();
        $terlihat = fn () => IzinKerja::terlihatOleh($request->user())->with('pemohon');

        // Izin yang sudah disahkan. "Berjalan" bila jadwal mulainya sudah tiba.
        $disahkan = $terlihat()->where('status', 'aktif')->orderBy('selesai_at')->get();
        $berjalan = $disahkan->filter(fn (IzinKerja $i) => $i->mulai_at->lte($sekarang))->values();
        $terjadwal = $disahkan->filter(fn (IzinKerja $i) => $i->mulai_at->gt($sekarang))->sortBy('mulai_at')->values();

        $tahap = config('izin.tahap_persetujuan');
        $antrian = $terlihat()->whereIn('status', array_keys($tahap))->orderBy('updated_at')->get();
        $penutupan = $terlihat()->where('status', 'menunggu_penutupan')->orderBy('penutupan_diajukan_at')->get();

        $kartuBerjalan = $berjalan->map(fn (IzinKerja $i) => $this->kartuBerjalan($i, $sekarang));

        return Inertia::render('Monitoring', [
            'diperbarui' => $sekarang->toIso8601String(),
            'ringkasan' => [
                'berjalan' => $berjalan->count(),
                'pekerja' => $kartuBerjalan->sum('jumlah_pekerja'),
                'hampir_habis' => $kartuBerjalan->where('keadaan', 'hampir_habis')->count(),
                'lewat_waktu' => $kartuBerjalan->where('keadaan', 'lewat_waktu')->count(),
                'menunggu_persetujuan' => $antrian->count(),
                'menunggu_penutupan' => $penutupan->count(),
                'terjadwal_24_jam' => $terjadwal->filter(fn ($i) => $i->mulai_at->lte($sekarang->copy()->addDay()))->count(),
            ],
            // Lewat waktu paling atas, lalu yang paling cepat berakhir.
            'berjalan' => $kartuBerjalan->sortBy(fn ($k) => [$k['keadaan'] === 'lewat_waktu' ? 0 : 1, $k['sisa_menit']])->values(),
            'antrian' => collect($tahap)->map(fn ($t, $status) => [
                'status' => $status,
                'label' => $t['label'],
                'izin' => $antrian->where('status', $status)->map(fn (IzinKerja $i) => [
                    ...$i->ringkas(),
                    'menunggu_menit' => (int) $i->updated_at->diffInMinutes($sekarang),
                    // Mendesak: jadwal mulai tinggal kurang dari 2 jam (atau sudah lewat) tetapi belum disahkan.
                    'mendesak' => $i->mulai_at->lte($sekarang->copy()->addHours(2)),
                ])->values(),
            ])->values(),
            'penutupan' => $penutupan->map(fn (IzinKerja $i) => [
                ...$i->ringkas(),
                'ada_insiden' => $i->ada_insiden,
                'label_insiden' => $i->labelInsiden(),
                'menunggu_menit' => (int) ($i->penutupan_diajukan_at ?? $i->updated_at)->diffInMinutes($sekarang),
            ])->values(),
            'jadwal' => $this->jadwal24Jam($terjadwal, $antrian, $sekarang),
            'perLokasi' => $this->sebaran($kartuBerjalan, 'lokasi_pilihan'),
            'perJenis' => $this->sebaran($kartuBerjalan, 'label_jenis'),
        ]);
    }

    private function kartuBerjalan(IzinKerja $i, $sekarang): array
    {
        $sisa = (int) $sekarang->diffInMinutes($i->selesai_at, false);
        $total = max(1, $i->mulai_at->diffInMinutes($i->selesai_at));

        return [
            ...$i->ringkas(),
            'lokasi_pilihan' => $i->lokasi,
            'jumlah_pekerja' => collect(preg_split('/\R/', (string) $i->pekerja))->filter(fn ($n) => trim($n) !== '')->count(),
            'uji_gas' => $i->butuhUjiGas(),
            'sisa_menit' => $sisa,
            'progres' => (int) min(100, max(0, round($i->mulai_at->diffInMinutes($sekarang) / $total * 100))),
            'keadaan' => $sisa < 0 ? 'lewat_waktu' : ($sisa <= self::HAMPIR_HABIS_MENIT ? 'hampir_habis' : 'normal'),
        ];
    }

    /** Izin yang dijadwalkan mulai dalam 24 jam ke depan, baik sudah disahkan maupun masih diproses. */
    private function jadwal24Jam(Collection $terjadwal, Collection $antrian, $sekarang): Collection
    {
        $batas = $sekarang->copy()->addDay();

        return $terjadwal->concat($antrian)
            ->filter(fn (IzinKerja $i) => $i->mulai_at->lte($batas) && $i->selesai_at->gt($sekarang))
            ->sortBy('mulai_at')
            ->map(fn (IzinKerja $i) => [...$i->ringkas(), 'siap' => $i->status === 'aktif'])
            ->values();
    }

    private function sebaran(Collection $kartu, string $kolom): Collection
    {
        return $kartu->groupBy($kolom)
            ->map(fn ($grup, $kunci) => ['label' => $kunci, 'jumlah' => $grup->count(), 'pekerja' => $grup->sum('jumlah_pekerja')])
            ->sortByDesc('jumlah')
            ->values();
    }
}
