<?php

namespace App\Support;

use App\Models\IzinKerja;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Izin yang menunggu sesuatu dari pengguna ini. Dipakai halaman Tindakan,
 * Dasbor, dan angka merah di menu, supaya ketiganya selalu sama.
 */
class DaftarTindakan
{
    public function __construct(private User $user) {}

    /** Status yang diputuskan peran pengguna ini (persetujuan, dan penutupan untuk Pengawas). */
    private function statusKeputusan(): array
    {
        $status = collect(config('izin.tahap_persetujuan'))
            ->filter(fn ($tahap) => $tahap['peran'] === $this->user->peran)
            ->keys()
            ->all();

        if ($this->user->adalah('pengawas')) {
            $status[] = 'menunggu_penutupan';
        }

        return $status;
    }

    /** Izin orang lain yang menunggu keputusan pengguna ini. */
    public function menungguKeputusan(): Collection
    {
        $status = $this->statusKeputusan();

        if ($status === []) {
            return collect();
        }

        return IzinKerja::with('pemohon')
            ->whereIn('status', $status)
            ->where('pemohon_id', '!=', $this->user->id)
            ->orderBy('diajukan_at')
            ->get();
    }

    /** Izin milik sendiri yang ditolak dan perlu diperbaiki. */
    public function perluDiperbaiki(): Collection
    {
        return IzinKerja::with('pemohon')
            ->where('pemohon_id', $this->user->id)
            ->where('status', 'ditolak')
            ->latest('updated_at')
            ->get();
    }

    /** Izin aktif milik sendiri yang sudah lewat jam selesai dan harus segera ditutup. */
    public function lewatWaktu(): Collection
    {
        return IzinKerja::with('pemohon')
            ->where('pemohon_id', $this->user->id)
            ->where('status', 'aktif')
            ->where('selesai_at', '<', now())
            ->orderBy('selesai_at')
            ->get();
    }

    public function draf(): Collection
    {
        return IzinKerja::with('pemohon')
            ->where('pemohon_id', $this->user->id)
            ->where('status', 'draf')
            ->latest('updated_at')
            ->get();
    }

    /** Angka merah di menu. Draf tidak dihitung karena tidak ada yang menunggunya. */
    public function jumlah(): int
    {
        $status = $this->statusKeputusan();

        $keputusan = $status === [] ? 0 : IzinKerja::whereIn('status', $status)
            ->where('pemohon_id', '!=', $this->user->id)
            ->count();

        $milikSendiri = IzinKerja::where('pemohon_id', $this->user->id)
            ->where(fn ($q) => $q->where('status', 'ditolak')
                ->orWhere(fn ($q) => $q->where('status', 'aktif')->where('selesai_at', '<', now())))
            ->count();

        return $keputusan + $milikSendiri;
    }
}
