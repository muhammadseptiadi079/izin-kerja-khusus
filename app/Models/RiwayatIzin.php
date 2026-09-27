<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatIzin extends Model
{
    protected $table = 'riwayat_izin';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public const AKSI = [
        'buat' => 'Membuat draf',
        'ajukan' => 'Mengajukan izin',
        'setujui' => 'Menyetujui',
        'tolak' => 'Menolak',
        'batalkan' => 'Membatalkan',
        'ajukan_penutupan' => 'Menyatakan pekerjaan selesai',
        'tutup' => 'Mengonfirmasi penutupan',
        'hentikan' => 'Menghentikan pekerjaan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function labelAksi(): string
    {
        return self::AKSI[$this->aksi] ?? $this->aksi;
    }
}
