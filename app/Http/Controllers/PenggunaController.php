<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $this->pastikanAdmin($request);

        return view('pengguna.index', ['pengguna' => User::orderBy('peran')->orderBy('name')->get()]);
    }

    public function create(Request $request)
    {
        $this->pastikanAdmin($request);

        return view('pengguna.form', ['pengguna' => new User(['peran' => 'pemohon', 'aktif' => true])]);
    }

    public function store(Request $request)
    {
        $this->pastikanAdmin($request);
        User::create($this->validasi($request));

        return redirect()->route('pengguna.index')->with('pesan', 'Pengguna ditambahkan.');
    }

    public function edit(Request $request, User $pengguna)
    {
        $this->pastikanAdmin($request);

        return view('pengguna.form', compact('pengguna'));
    }

    public function update(Request $request, User $pengguna)
    {
        $this->pastikanAdmin($request);
        $data = $this->validasi($request, $pengguna);

        if ($pengguna->is($request->user()) && ($data['peran'] !== 'admin' || ! $data['aktif'])) {
            return back()->withErrors(['peran' => 'Anda tidak dapat mencabut peran admin atau menonaktifkan akun sendiri.']);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $pengguna->update($data);

        return redirect()->route('pengguna.index')->with('pesan', 'Pengguna diperbarui.');
    }

    private function validasi(Request $request, ?User $pengguna = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($pengguna)],
            'peran' => ['required', Rule::in(array_keys(User::PERAN))],
            'nik' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($pengguna)],
            'nomor_wa' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'departemen' => ['nullable', Rule::in(config('izin.departemen'))],
            'password' => [$pengguna ? 'nullable' : 'required', Password::min(8)],
        ], [], ['name' => 'nama', 'nik' => 'NIK', 'nomor_wa' => 'nomor WA', 'password' => 'kata sandi']);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }

    private function pastikanAdmin(Request $request): void
    {
        abort_unless($request->user()->adalah('admin'), 403, 'Hanya administrator yang dapat mengelola pengguna.');
    }
}
