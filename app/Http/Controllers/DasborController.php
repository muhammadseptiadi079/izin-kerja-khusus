<?php

namespace App\Http\Controllers;

use App\Models\IzinKerja;
use App\Support\DaftarTindakan;
use App\Support\Katalog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DasborController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $terlihat = IzinKerja::terlihatOleh($user);

        $tindakan = new DaftarTindakan($user);
        $perluTindakan = $tindakan->menungguKeputusan();

        $aktif = (clone $terlihat)->with('pemohon')
            ->where('status', 'aktif')
            ->orderBy('selesai_at')
            ->get();

        $izinSaya = $tindakan->perluDiperbaiki()->concat($tindakan->draf());

        return Inertia::render('Dasbor', [
            'perluTindakan' => $perluTindakan->map->ringkas(),
            'aktif' => $aktif->map->ringkas(),
            'izinSaya' => $izinSaya->map->ringkas(),
            'jenis' => collect(Katalog::jenis())->map(fn ($j) => collect($j)->only('kunci', 'label', 'label_en', 'gambar')),
            'ringkasan' => [
                'aktif' => $aktif->count(),
                'lewat_waktu' => $aktif->filter->lewatWaktu()->count(),
                'menunggu' => (clone $terlihat)->whereIn('status', array_keys(config('izin.tahap_persetujuan')))->count(),
                'selesai_bulan_ini' => (clone $terlihat)->where('status', 'selesai')->where('ditutup_at', '>=', now()->startOfMonth())->count(),
            ],
        ]);
    }
}
