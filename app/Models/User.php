<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'peran', 'nik', 'nomor_wa', 'jabatan', 'departemen', 'aktif'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PERAN = [
        'pemohon' => 'Pemohon',
        'pengawas' => 'Pengawas Area',
        'hse' => 'HSE',
        'manajer' => 'Manajer / Penanggung Jawab',
        'admin' => 'Administrator',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function izinKerja(): HasMany
    {
        return $this->hasMany(IzinKerja::class, 'pemohon_id');
    }

    public function labelPeran(): string
    {
        return self::PERAN[$this->peran] ?? $this->peran;
    }

    public function adalah(string ...$peran): bool
    {
        return in_array($this->peran, $peran, true);
    }

    /** Pemohon hanya melihat izinnya sendiri; peran lain melihat semua. */
    public function melihatSemuaIzin(): bool
    {
        return ! $this->adalah('pemohon');
    }
}
