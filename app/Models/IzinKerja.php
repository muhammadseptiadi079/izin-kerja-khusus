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

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenIzin::class);
    }

    public function dokumenJenis(string $jenis): ?DokumenIzin
    {
        return $this->dokumen->firstWhere('jenis', $jenis);
    }

    public function lokasiLengkap(): string
    {
        return $this->lokasi.($this->lokasi_detail ? ' — '.$this->lokasi_detail : '');
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

    /** Data ringkas untuk tabel dan kartu. */
    public function ringkas(): array
    {
        return [
            'id' => $this->id,
            'nomor' => $this->nomor,
            'jenis' => $this->jenis,
            'label_jenis' => $this->labelJenis(),
            'status' => $this->status,
            'label_status' => $this->labelStatus(),
            'lewat_waktu' => $this->lewatWaktu(),
            'lokasi' => $this->lokasiLengkap(),
            'uraian_pekerjaan' => $this->uraian_pekerjaan,
            'pemohon' => $this->pemohon?->name,
            'mulai_at' => $this->mulai_at?->toIso8601String(),
            'selesai_at' => $this->selesai_at?->toIso8601String(),
            'diajukan_at' => $this->diajukan_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /** Data lengkap untuk halaman detail dan formulir. */
    public function lengkap(): array
    {
        return [
            ...$this->ringkas(),
            'nik' => $this->nik,
            'nomor_wa' => $this->nomor_wa,
            'departemen' => $this->departemen,
            'lokasi_pilihan' => $this->lokasi,
            'lokasi_detail' => $this->lokasi_detail,
            'peralatan' => $this->peralatan,
            'pekerja' => $this->pekerja,
            'bahaya' => $this->bahaya ?? [],
            'bahaya_lain' => $this->bahaya_lain,
            'pengendalian' => $this->pengendalian ?? [],
            'pengendalian_tambahan' => $this->pengendalian_tambahan,
            'apd' => $this->apd ?? [],
            'uji_gas' => $this->uji_gas,
            'uji_gas_oleh' => $this->uji_gas_oleh,
            'uji_gas_at' => $this->uji_gas_at?->toIso8601String(),
            'disahkan_at' => $this->disahkan_at?->toIso8601String(),
            'ditutup_at' => $this->ditutup_at?->toIso8601String(),
            'catatan_penutupan' => $this->catatan_penutupan,
            'pemohon_jabatan' => $this->pemohon?->jabatan,
            'dokumen' => $this->dokumen->map(fn (DokumenIzin $d) => [
                'jenis' => $d->jenis,
                'label' => $d->label(),
                'nama_asli' => $d->nama_asli,
                'ukuran' => $d->ukuranTerbaca(),
                'url' => route('izin.dokumen', [$this->id, $d->jenis]),
            ])->values()->all(),
            'riwayat' => $this->riwayat->map(fn (RiwayatIzin $r) => [
                'id' => $r->id,
                'aksi' => $r->aksi,
                'label_aksi' => $r->labelAksi(),
                'status_ke' => self::STATUS[$r->status_ke] ?? $r->status_ke,
                'catatan' => $r->catatan,
                'oleh' => $r->user->name,
                'peran' => $r->user->labelPeran(),
                'waktu' => $r->created_at->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function bisaDiubah(): bool
    {
        return in_array($this->status, ['draf', 'ditolak'], true);
    }
}
