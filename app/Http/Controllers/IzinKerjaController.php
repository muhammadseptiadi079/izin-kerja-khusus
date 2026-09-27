<?php

namespace App\Http\Controllers;

use App\Models\IzinKerja;
use App\Support\AlurIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IzinKerjaController extends Controller
{
    public function __construct(private AlurIzin $alur) {}

    public function index(Request $request)
    {
        $izin = IzinKerja::terlihatOleh($request->user())
            ->with('pemohon')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->jenis, fn ($q, $jenis) => $q->where('jenis', $jenis))
            ->when($request->cari, function ($q, $cari) {
                $q->where(fn ($q) => $q->where('nomor', 'like', "%{$cari}%")
                    ->orWhere('lokasi', 'like', "%{$cari}%")
                    ->orWhere('uraian_pekerjaan', 'like', "%{$cari}%"));
            })
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('izin.index', compact('izin'));
    }

    public function create(Request $request)
    {
        $jenis = $request->query('jenis');

        if (! config('izin.jenis.'.$jenis)) {
            return view('izin.pilih-jenis');
        }

        $user = $request->user();
        $izin = new IzinKerja([
            'jenis' => $jenis,
            'nik' => $user->nik,
            'nomor_wa' => $user->nomor_wa,
            'departemen' => $user->departemen,
            'mulai_at' => now()->addHour()->startOfHour(),
            'selesai_at' => now()->addHours(9)->startOfHour(),
        ]);

        return view('izin.form', compact('izin'));
    }

    public function store(Request $request)
    {
        $izin = new IzinKerja($this->validasi($request));
        $izin->pemohon_id = $request->user()->id;
        $izin->status = 'draf';
        $izin->save();
        $this->simpanDokumen($request, $izin);

        $this->alur->catat($izin, $request->user(), 'buat', null);

        return $this->setelahSimpan($request, $izin);
    }

    public function show(Request $request, IzinKerja $izin)
    {
        $this->pastikanTerlihat($request, $izin);
        $izin->load('pemohon', 'riwayat.user', 'dokumen');
        $aksi = $this->alur->aksiTersedia($izin, $request->user());

        return view('izin.show', compact('izin', 'aksi'));
    }

    public function edit(Request $request, IzinKerja $izin)
    {
        $this->pastikanBisaDiubah($request, $izin);
        $izin->load('dokumen', 'pemohon');

        return view('izin.form', compact('izin'));
    }

    public function update(Request $request, IzinKerja $izin)
    {
        $this->pastikanBisaDiubah($request, $izin);
        $izin->update($this->validasi($request, $izin->jenis));
        $this->simpanDokumen($request, $izin);

        return $this->setelahSimpan($request, $izin);
    }

    public function cetak(Request $request, IzinKerja $izin)
    {
        $this->pastikanTerlihat($request, $izin);
        $izin->load('pemohon', 'riwayat.user', 'dokumen');

        return view('izin.cetak', compact('izin'));
    }

    public function unduh(Request $request, IzinKerja $izin, string $jenis)
    {
        $this->pastikanTerlihat($request, $izin);
        $dokumen = $izin->dokumen()->where('jenis', $jenis)->firstOrFail();

        return Storage::disk('local')->download($dokumen->path, $dokumen->nama_asli);
    }

    public function aksi(Request $request, IzinKerja $izin)
    {
        $this->pastikanTerlihat($request, $izin);

        $data = $request->validate([
            'aksi' => ['required', Rule::in(array_keys(\App\Models\RiwayatIzin::AKSI))],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->alur->jalankan($izin, $request->user(), $data['aksi'], $data['catatan'] ?? null);

        return redirect()->route('izin.show', $izin)
            ->with('pesan', \App\Models\RiwayatIzin::AKSI[$data['aksi']].' berhasil. Status sekarang: '.$izin->labelStatus().'.');
    }

    private function setelahSimpan(Request $request, IzinKerja $izin)
    {
        if (! $request->boolean('ajukan')) {
            return redirect()->route('izin.show', $izin)->with('pesan', 'Draf izin disimpan.');
        }

        try {
            $this->alur->jalankan($izin, $request->user(), 'ajukan');
        } catch (ValidationException $e) {
            // Draf tetap tersimpan; pemohon melengkapi yang kurang lalu mengajukan lagi.
            return redirect()->route('izin.edit', $izin)
                ->withErrors($e->errors())
                ->with('pesan', 'Draf disimpan, tetapi izin belum bisa diajukan.');
        }

        return redirect()->route('izin.show', $izin)
            ->with('pesan', 'Izin '.$izin->nomor.' diajukan dan menunggu persetujuan Pengawas Area.');
    }

    private function validasi(Request $request, ?string $jenis = null): array
    {
        $jenis ??= $request->input('jenis');
        $aturan = config('izin.jenis.'.$jenis);

        if (! $aturan) {
            throw ValidationException::withMessages(['jenis' => 'Jenis izin tidak dikenal.']);
        }

        $data = $request->validate([
            'jenis' => ['sometimes', Rule::in(array_keys(config('izin.jenis')))],
            'nik' => ['required', 'string', 'max:50'],
            'nomor_wa' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'departemen' => ['required', Rule::in(config('izin.departemen'))],
            'lokasi' => ['required', Rule::in(config('izin.lokasi'))],
            'lokasi_detail' => ['nullable', 'string', 'max:255'],
            'dokumen' => ['nullable', 'array'],
            'dokumen.*' => ['nullable', 'file', 'mimes:'.implode(',', config('izin.dokumen_ekstensi')), 'max:'.config('izin.dokumen_maks_kb')],
            'uraian_pekerjaan' => ['required', 'string', 'max:5000'],
            'peralatan' => ['nullable', 'string', 'max:2000'],
            'pekerja' => ['required', 'string', 'max:2000'],
            'mulai_at' => ['required', 'date'],
            'selesai_at' => ['required', 'date', 'after:mulai_at'],
            'bahaya' => ['nullable', 'array'],
            'bahaya.*' => [Rule::in($aturan['bahaya'])],
            'bahaya_lain' => ['nullable', 'string', 'max:2000'],
            'pengendalian' => ['nullable', 'array'],
            'pengendalian.*' => [Rule::in($aturan['pengendalian'])],
            'pengendalian_tambahan' => ['nullable', 'string', 'max:2000'],
            'apd' => ['nullable', 'array'],
            'apd.*' => [Rule::in(config('izin.apd'))],
            'uji_gas' => ['nullable', 'array'],
            'uji_gas.*' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'uji_gas_oleh' => ['nullable', 'string', 'max:255'],
            'uji_gas_at' => ['nullable', 'date'],
        ], [], [
            'mulai_at' => 'waktu mulai',
            'selesai_at' => 'waktu selesai',
            'uraian_pekerjaan' => 'uraian pekerjaan',
            'nik' => 'NIK',
            'nomor_wa' => 'nomor WA',
            'dokumen.sop' => config('izin.dokumen.sop'),
            'dokumen.fit_to_work' => config('izin.dokumen.fit_to_work'),
            'dokumen.jsea' => config('izin.dokumen.jsea'),
        ]);

        unset($data['dokumen']);

        $data['jenis'] = $jenis;
        $data['bahaya'] = array_values($data['bahaya'] ?? []);
        $data['pengendalian'] = array_values($data['pengendalian'] ?? []);
        $data['apd'] = array_values($data['apd'] ?? []);

        if (empty($aturan['uji_gas'])) {
            $data['uji_gas'] = null;
            $data['uji_gas_oleh'] = null;
            $data['uji_gas_at'] = null;
        } else {
            $data['uji_gas'] = collect(config('izin.uji_gas'))
                ->mapWithKeys(fn ($_, $kunci) => [$kunci => $data['uji_gas'][$kunci] ?? null])
                ->all();
        }

        return $data;
    }

    /** Berkas baru menggantikan berkas lama dengan jenis yang sama. */
    private function simpanDokumen(Request $request, IzinKerja $izin): void
    {
        foreach (array_keys(config('izin.dokumen')) as $jenis) {
            $berkas = $request->file('dokumen.'.$jenis);

            if (! $berkas) {
                continue;
            }

            $lama = $izin->dokumen()->where('jenis', $jenis)->first();
            $path = $berkas->store('dokumen-izin/'.$izin->id, 'local');

            $izin->dokumen()->updateOrCreate(['jenis' => $jenis], [
                'nama_asli' => $berkas->getClientOriginalName(),
                'path' => $path,
                'ukuran' => $berkas->getSize(),
            ]);

            if ($lama) {
                Storage::disk('local')->delete($lama->path);
            }
        }
    }

    private function pastikanTerlihat(Request $request, IzinKerja $izin): void
    {
        abort_unless(
            $request->user()->melihatSemuaIzin() || $izin->pemohon_id === $request->user()->id,
            404
        );
    }

    private function pastikanBisaDiubah(Request $request, IzinKerja $izin): void
    {
        abort_unless($izin->pemohon_id === $request->user()->id, 403, 'Hanya pemohon yang dapat mengubah izin ini.');
        abort_unless($izin->bisaDiubah(), 403, 'Izin dengan status '.$izin->labelStatus().' tidak dapat diubah.');
    }
}
