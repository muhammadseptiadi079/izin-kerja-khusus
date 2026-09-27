<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenIzin extends Model
{
    protected $table = 'dokumen_izin';

    protected $guarded = ['id'];

    public function izinKerja(): BelongsTo
    {
        return $this->belongsTo(IzinKerja::class);
    }

    public function label(): string
    {
        return config('izin.dokumen.'.$this->jenis, $this->jenis);
    }

    public function ukuranTerbaca(): string
    {
        return $this->ukuran >= 1048576
            ? number_format($this->ukuran / 1048576, 1, ',', '.').' MB'
            : number_format(max(1, $this->ukuran / 1024), 0, ',', '.').' KB';
    }
}
