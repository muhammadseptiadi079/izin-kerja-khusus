<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IzinKerja extends Model
{
    protected $table = 'izin_kerja';

    protected $guarded = ['id', 'nomor', 'pemohon_id', 'status', 'diajukan_at', 'disahkan_at', 'ditutup_at'];

    public const STATUS = [
        'draf' => 'Draf',
        'menunggu_pengawas' => 'Menunggu Pengawas',
        'menunggu_hse' => 'Menunggu HSE',
        'menunggu_manajer' => 'Menunggu Manajer',
        'aktif' => 'Aktif',
        'menunggu_penutupan' => 'Menunggu Penutupan',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
        'dihentikan' => 'Dihentikan',
    ];

    protected function casts(): array
    {
        return [
            'mulai_at' => 'datetime',
            'selesai_at' => 'datetime',
            'uji_gas_at' => 'datetime',
            'diajukan_at' => 'datetime',
            'disahkan_at' => 'datetime',
            'ditutup_at' => 'datetime',
            'bahaya' => 'array',
            'pengendalian' => 'array',
            'apd' => 'array',
            'uji_gas' => 'array',
        ];
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatIzin::class)->orderBy('created_at')->orderBy('id');
    }

    public function scopeTerlihatOleh(Builder $query, User $user): Builder
    {
        return $user->melihatSemuaIzin() ? $query : $query->where('pemohon_id', $user->id);
    }

    public function aturan(): array
    {
        return config('izin.jenis.'.$this->jenis, []);
    }

    public function labelJenis(): string
    {
        return $this->aturan()['label'] ?? $this->jenis;
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function butuhUjiGas(): bool
    {
        return (bool) ($this->aturan()['uji_gas'] ?? false);
    }

    public function menungguPersetujuan(): bool
    {
        return array_key_exists($this->status, config('izin.tahap_persetujuan'));
    }

    /** Izin aktif yang sudah melewati jam selesai tetapi belum ditutup. */
    public function lewatWaktu(): bool
    {
        return $this->status === 'aktif' && $this->selesai_at->isPast();
    }

    public function bisaDiubah(): bool
    {
        return in_array($this->status, ['draf', 'ditolak'], true);
    }
}
