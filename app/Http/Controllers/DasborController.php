<?php

namespace App\Http\Controllers;

use App\Models\IzinKerja;
use Illuminate\Http\Request;

class DasborController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $terlihat = IzinKerja::terlihatOleh($user);

        // Tahap yang sedang menunggu keputusan peran pengguna ini.
        $tahapSaya = collect(config('izin.tahap_persetujuan'))
            ->filter(fn ($tahap) => $tahap['peran'] === $user->peran)
            ->keys()
            ->all();

        if ($user->adalah('pengawas')) {
            $tahapSaya[] = 'menunggu_penutupan';
        }

        $perluTindakan = IzinKerja::with('pemohon')
            ->whereIn('status', $tahapSaya)
            ->where('pemohon_id', '!=', $user->id)
            ->orderBy('diajukan_at')
            ->get();

        $aktif = (clone $terlihat)->with('pemohon')
            ->where('status', 'aktif')
            ->orderBy('selesai_at')
            ->get();

        $izinSaya = IzinKerja::where('pemohon_id', $user->id)
            ->whereIn('status', ['draf', 'ditolak'])
            ->latest('updated_at')
            ->get();

        $ringkasan = [
            'aktif' => $aktif->count(),
            'lewat_waktu' => $aktif->filter->lewatWaktu()->count(),
            'menunggu' => (clone $terlihat)->whereIn('status', array_keys(config('izin.tahap_persetujuan')))->count(),
            'selesai_bulan_ini' => (clone $terlihat)->where('status', 'selesai')->where('ditutup_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('dasbor', compact('perluTindakan', 'aktif', 'izinSaya', 'ringkasan'));
    }
}
