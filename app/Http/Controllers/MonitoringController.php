<?php

namespace App\Http\Controllers;

use App\Models\IzinKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $izin = $this->query($request, $dari, $sampai)->get();

        $rekap = fn (string $kolom) => $izin->groupBy($kolom)->map->count()->sortDesc();

        $diajukan = $izin->whereNotNull('diajukan_at');
        $disahkan = $izin->whereNotNull('disahkan_at');

        $evaluasi = [
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
        ];

        return view('monitoring', [
            'dari' => $dari,
            'sampai' => $sampai,
            'evaluasi' => $evaluasi,
            'perJenis' => $rekap('jenis'),
            'perLokasi' => $rekap('lokasi'),
            'perDepartemen' => $rekap('departemen'),
            'perStatus' => $rekap('status'),
            'perHari' => $izin->groupBy(fn ($i) => $i->mulai_at->format('Y-m-d'))->map->count()->sortKeys(),
        ]);
    }

    public function ekspor(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $izin = $this->query($request, $dari, $sampai)->orderBy('mulai_at')->get();
        $nama = 'rekap-ikk-'.$dari->format('Ymd').'-'.$sampai->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($izin) {
            $keluar = fopen('php://output', 'w');
            fwrite($keluar, "\xEF\xBB\xBF"); // agar Excel membaca UTF-8
            fputcsv($keluar, ['Nomor', 'Jenis', 'Status', 'Nama', 'NIK', 'Nomor WA', 'Departemen', 'Lokasi', 'Pekerjaan', 'Mulai', 'Selesai', 'Diajukan', 'Disahkan', 'Ditutup'], ';');

            foreach ($izin as $i) {
                fputcsv($keluar, [
                    $i->nomor, $i->labelJenis(), $i->labelStatus(), $i->pemohon->name, $i->nik, $i->nomor_wa,
                    $i->departemen, $i->lokasiLengkap(), $i->uraian_pekerjaan,
                    $i->mulai_at->format('d/m/Y H:i'), $i->selesai_at->format('d/m/Y H:i'),
                    $i->diajukan_at?->format('d/m/Y H:i'), $i->disahkan_at?->format('d/m/Y H:i'), $i->ditutup_at?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($keluar);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
