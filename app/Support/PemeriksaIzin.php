<?php

namespace App\Support;

use App\Models\IzinKerja;

/**
 * Memeriksa apakah izin sudah layak diajukan: semua pengendalian wajib
 * dicentang, durasi tidak melebihi batas jenisnya, dan uji gas aman.
 */
class PemeriksaIzin
{
    /** @return list<string> daftar masalah; kosong berarti layak diajukan. */
    public function masalah(IzinKerja $izin): array
    {
        $aturan = $izin->aturan();
        $masalah = [];

        $belum = array_diff($aturan['pengendalian'] ?? [], $izin->pengendalian ?? []);
        foreach ($belum as $item) {
            $masalah[] = 'Pengendalian belum dipastikan: '.$item.'.';
        }

        if (empty($izin->apd)) {
            $masalah[] = 'Pilih minimal satu APD yang wajib dipakai.';
        }

        if ($izin->selesai_at->lte($izin->mulai_at)) {
            $masalah[] = 'Waktu selesai harus setelah waktu mulai.';
        } elseif ($izin->mulai_at->diffInMinutes($izin->selesai_at) > ($aturan['durasi_maks_jam'] ?? 12) * 60) {
            $masalah[] = 'Durasi izin '.$izin->labelJenis().' maksimal '.$aturan['durasi_maks_jam'].' jam. Ajukan izin baru untuk shift berikutnya.';
        }

        if ($izin->selesai_at->isPast()) {
            $masalah[] = 'Waktu selesai sudah lewat. Perbarui jadwal pekerjaan.';
        }

        if ($izin->butuhUjiGas()) {
            $masalah = [...$masalah, ...$this->masalahUjiGas($izin)];
        }

        return $masalah;
    }

    /** @return list<string> */
    public function masalahUjiGas(IzinKerja $izin): array
    {
        $masalah = [];
        $hasil = $izin->uji_gas ?? [];

        foreach (config('izin.uji_gas') as $kunci => $batas) {
            $nilai = $hasil[$kunci] ?? null;

            if ($nilai === null || $nilai === '') {
                $masalah[] = 'Hasil uji gas '.$batas['label'].' belum diisi.';
            } elseif (! self::aman($kunci, (float) $nilai)) {
                $masalah[] = 'Uji gas '.$batas['label'].' = '.$nilai.' '.$batas['satuan'].' di luar batas aman ('.$batas['min'].'–'.$batas['maks'].' '.$batas['satuan'].'). Pekerjaan tidak boleh dimulai.';
            }
        }

        if (blank($izin->uji_gas_oleh) || $izin->uji_gas_at === null) {
            $masalah[] = 'Isi nama penguji gas dan waktu pengujian.';
        }

        return $masalah;
    }

    public static function aman(string $kunci, float $nilai): bool
    {
        $batas = config('izin.uji_gas.'.$kunci);

        return $nilai >= $batas['min'] && $nilai <= $batas['maks'];
    }
}
