<?php

namespace App\Support;

use App\Models\IzinKerja;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Satu-satunya tempat status izin berpindah. Setiap perpindahan dicatat di
 * riwayat bersama siapa yang melakukannya.
 */
class AlurIzin
{
    /** Aksi yang boleh dilakukan pengguna ini terhadap izin ini sekarang. */
    public function aksiTersedia(IzinKerja $izin, User $user): array
    {
        $milikSendiri = $izin->pemohon_id === $user->id;
        $aksi = [];

        if ($milikSendiri && $izin->bisaDiubah()) {
            $aksi[] = 'ajukan';
        }

        if ($this->bolehMemutuskan($izin, $user)) {
            $aksi[] = 'setujui';
            $aksi[] = 'tolak';
        }

        if ($milikSendiri && ($izin->bisaDiubah() || $izin->menungguPersetujuan())) {
            $aksi[] = 'batalkan';
        }

        if ($milikSendiri && $izin->status === 'aktif') {
            $aksi[] = 'ajukan_penutupan';
        }

        if ($izin->status === 'menunggu_penutupan' && $user->adalah('pengawas') && ! $milikSendiri) {
            $aksi[] = 'tutup';
        }

        if ($izin->status === 'aktif' && $user->adalah('pengawas', 'hse', 'manajer')) {
            $aksi[] = 'hentikan';
        }

        return $aksi;
    }

    public function boleh(IzinKerja $izin, User $user, string $aksi): bool
    {
        return in_array($aksi, $this->aksiTersedia($izin, $user), true);
    }

    public function jalankan(IzinKerja $izin, User $user, string $aksi, ?string $catatan = null): IzinKerja
    {
        if (! $this->boleh($izin, $user, $aksi)) {
            abort(403, 'Anda tidak dapat melakukan aksi ini pada izin dengan status '.$izin->labelStatus().'.');
        }

        if (in_array($aksi, ['tolak', 'hentikan', 'ajukan_penutupan'], true) && blank($catatan)) {
            throw ValidationException::withMessages(['catatan' => 'Catatan wajib diisi untuk aksi ini.']);
        }

        return DB::transaction(function () use ($izin, $user, $aksi, $catatan) {
            $dari = $izin->status;

            match ($aksi) {
                'ajukan' => $this->ajukan($izin),
                'setujui' => $this->setujui($izin),
                'tolak' => $izin->status = 'ditolak',
                'batalkan' => $izin->status = 'dibatalkan',
                'ajukan_penutupan' => $this->ajukanPenutupan($izin, $catatan),
                'tutup' => $this->tutup($izin),
                'hentikan' => $this->hentikan($izin),
            };

            $izin->save();
            $this->catat($izin, $user, $aksi, $dari, $catatan);

            return $izin;
        });
    }

    public function catat(IzinKerja $izin, User $user, string $aksi, ?string $dari, ?string $catatan = null): void
    {
        $izin->riwayat()->create([
            'user_id' => $user->id,
            'aksi' => $aksi,
            'status_dari' => $dari,
            'status_ke' => $izin->status,
            'catatan' => $catatan,
        ]);
    }

    private function bolehMemutuskan(IzinKerja $izin, User $user): bool
    {
        $tahap = config('izin.tahap_persetujuan.'.$izin->status);

        // Pemohon tidak boleh menyetujui izinnya sendiri, apa pun perannya.
        return $tahap !== null
            && $user->peran === $tahap['peran']
            && $izin->pemohon_id !== $user->id;
    }

    private function ajukan(IzinKerja $izin): void
    {
        $masalah = (new PemeriksaIzin)->masalah($izin);

        if ($masalah !== []) {
            throw ValidationException::withMessages(['ajukan' => $masalah]);
        }

        $izin->nomor ??= $this->nomorBaru($izin);
        $izin->status = 'menunggu_pengawas';
        $izin->diajukan_at = now();
    }

    private function setujui(IzinKerja $izin): void
    {
        if ($izin->selesai_at->isPast()) {
            throw ValidationException::withMessages([
                'setujui' => 'Waktu selesai izin sudah lewat. Tolak izin ini agar pemohon memperbarui jadwalnya.',
            ]);
        }

        $izin->status = config('izin.tahap_persetujuan.'.$izin->status.'.berikutnya');

        if ($izin->status === 'aktif') {
            $izin->disahkan_at = now();
        }
    }

    private function ajukanPenutupan(IzinKerja $izin, string $catatan): void
    {
        $izin->status = 'menunggu_penutupan';
        $izin->catatan_penutupan = $catatan;
    }

    private function tutup(IzinKerja $izin): void
    {
        $izin->status = 'selesai';
        $izin->ditutup_at = now();
    }

    private function hentikan(IzinKerja $izin): void
    {
        $izin->status = 'dihentikan';
        $izin->ditutup_at = now();
    }

    /** Nomor berurutan per jenis per bulan, misal KP-202609-0007. */
    private function nomorBaru(IzinKerja $izin): string
    {
        $awalan = ($izin->aturan()['kode'] ?? 'IK').'-'.now()->format('Ym').'-';

        $terakhir = IzinKerja::where('nomor', 'like', $awalan.'%')
            ->lockForUpdate()
            ->orderByDesc('nomor')
            ->value('nomor');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan.str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }
}
