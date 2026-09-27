<?php

namespace App\Support;

/**
 * Gambar untuk setiap jenis izin diambil dari public/img/jenis/{kunci}.{jpg,png,webp,svg}.
 * Foto (jpg/png/webp) didahulukan, sehingga cukup menaruh foto baru untuk
 * menggantikan ilustrasi bawaan.
 */
class GambarJenis
{
    public static function url(string $kunci): ?string
    {
        foreach (['jpg', 'jpeg', 'png', 'webp', 'svg'] as $ekstensi) {
            $berkas = 'img/jenis/'.$kunci.'.'.$ekstensi;

            if (is_file(public_path($berkas))) {
                return asset($berkas).'?v='.filemtime(public_path($berkas));
            }
        }

        return null;
    }
}
