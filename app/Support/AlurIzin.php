<?php

namespace App\Support;

use App\Models\IzinKerja;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

    /**
     * @param  array<string, mixed>  $evaluasi  Isian evaluasi pasca pekerjaan untuk ajukan_penutupan dan hentikan.
     */
    public function jalankan(IzinKerja $izin, User $user, string $aksi, ?string $catatan = null, array $evaluasi = []): IzinKerja
    {
        if (! $this->boleh($izin, $user, $aksi)) {
            abort(403, 'Anda tidak dapat melakukan aksi ini pada izin dengan status '.$izin->labelStatus().'.');
        }

        if (in_array($aksi, ['tolak', 'hentikan', 'ajukan_penutupan'], true) && blank($catatan)) {
            throw ValidationException::withMessages(['catatan' => 'Catatan wajib diisi untuk aksi ini.']);
        }

        if (in_array($aksi, ['ajukan_penutupan', 'hentikan'], true)) {
            $evaluasi = $this->validasiEvaluasi($izin, $aksi, $evaluasi);
        }

        return DB::transaction(function () use ($izin, $user, $aksi, $catatan, $evaluasi) {
            $dari = $izin->status;

            match ($aksi) {
                'ajukan' => $this->ajukan($izin),
                'setujui' => $this->setujui($izin),
                'tolak' => $izin->status = 'ditolak',
                'batalkan' => $izin->status = 'dibatalkan',
                'ajukan_penutupan' => $this->ajukanPenutupan($izin, $catatan, $evaluasi),
                'tutup' => $this->tutup($izin),
                'hentikan' => $this->hentikan($izin, $evaluasi),
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

    private function ajukanPenutupan(IzinKerja $izin, string $catatan, array $evaluasi): void
    {
        $izin->status = 'menunggu_penutupan';
        $izin->catatan_penutupan = $catatan;
        $izin->penutupan_diajukan_at = now();
        $this->isiEvaluasi($izin, $evaluasi);
    }

    /**
     * Evaluasi pasca pekerjaan. Saat penutupan, pemohon wajib melaporkan jam
     * kerja sebenarnya, pemeriksaan area, dan ada tidaknya insiden. Saat
     * penghentian, cukup ada tidaknya insiden; jam selesai dicatat saat itu.
     *
     * @return array<string, mixed>
     */
    private function validasiEvaluasi(IzinKerja $izin, string $aksi, array $data): array
    {
        $penutupan = $aksi === 'ajukan_penutupan';

        $aturan = [
            'ada_insiden' => ['required', 'boolean'],
            'kategori_insiden' => ['required_if:ada_insiden,true,1', 'nullable', Rule::in(array_keys(config('izin.insiden')))],
            'uraian_insiden' => ['required_if:ada_insiden,true,1', 'nullable', 'string', 'max:3000'],
            'tindakan_insiden' => ['required_if:ada_insiden,true,1', 'nullable', 'string', 'max:3000'],
        ];

        if ($penutupan) {
            $aturan += [
                'mulai_aktual_at' => ['required', 'date'],
                'selesai_aktual_at' => ['required', 'date', 'after:mulai_aktual_at', 'before_or_equal:'.now()->addMinutes(5)->toDateTimeString()],
                'pemeriksaan_penutupan' => ['required', 'array'],
                'pemeriksaan_penutupan.*' => [Rule::in(config('izin.pemeriksaan_penutupan'))],
            ];
        }

        $hasil = Validator::make($data, $aturan, [
            'ada_insiden.required' => 'Pilih apakah terjadi insiden selama pekerjaan.',
            'required_if' => ':attribute wajib diisi bila terjadi insiden.',
            'selesai_aktual_at.before_or_equal' => 'Jam selesai sebenarnya tidak boleh di masa depan.',
            'selesai_aktual_at.after' => 'Jam selesai sebenarnya harus setelah jam mulai.',
            'pemeriksaan_penutupan.required' => 'Pastikan kondisi area sebelum mengajukan penutupan.',
        ], [
            'kategori_insiden' => 'Kategori insiden',
            'uraian_insiden' => 'Kronologi insiden',
            'tindakan_insiden' => 'Tindakan yang diambil',
            'mulai_aktual_at' => 'Jam mulai sebenarnya',
            'selesai_aktual_at' => 'Jam selesai sebenarnya',
        ])->validate();

        if ($penutupan) {
            $kurang = array_diff(config('izin.pemeriksaan_penutupan'), $hasil['pemeriksaan_penutupan']);
            if ($kurang !== []) {
                throw ValidationException::withMessages([
                    'pemeriksaan_penutupan' => 'Belum dipastikan: '.implode('; ', $kurang).'.',
                ]);
            }
        }

        return $hasil;
    }

    private function isiEvaluasi(IzinKerja $izin, array $evaluasi): void
    {
        $adaInsiden = filter_var($evaluasi['ada_insiden'], FILTER_VALIDATE_BOOLEAN);

        $izin->ada_insiden = $adaInsiden;
        $izin->kategori_insiden = $adaInsiden ? $evaluasi['kategori_insiden'] : null;
        $izin->uraian_insiden = $adaInsiden ? $evaluasi['uraian_insiden'] : null;
        $izin->tindakan_insiden = $adaInsiden ? $evaluasi['tindakan_insiden'] : null;

        if (isset($evaluasi['mulai_aktual_at'])) {
            $izin->mulai_aktual_at = $evaluasi['mulai_aktual_at'];
            $izin->selesai_aktual_at = $evaluasi['selesai_aktual_at'];
            $izin->pemeriksaan_penutupan = array_values($evaluasi['pemeriksaan_penutupan']);
        }
    }

    private function tutup(IzinKerja $izin): void
    {
        $izin->status = 'selesai';
        $izin->ditutup_at = now();
    }

    private function hentikan(IzinKerja $izin, array $evaluasi): void
    {
        $izin->status = 'dihentikan';
        $izin->ditutup_at = now();
        $izin->selesai_aktual_at = now();
        $this->isiEvaluasi($izin, $evaluasi);
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
