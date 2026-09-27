<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Pekerja mendaftar sendiri sebagai pemohon. Peran penyetuju (Pengawas,
 * HSE, Manajer) hanya bisa diberikan oleh admin.
 */
class DaftarController extends Controller
{
    public function create()
    {
        return view('auth.daftar');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:50', 'unique:users,nik'],
            'nomor_wa' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'departemen' => ['required', Rule::in(config('izin.departemen'))],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['name' => 'nama', 'nik' => 'NIK', 'nomor_wa' => 'nomor WA', 'password' => 'kata sandi']);

        $user = User::create([...$data, 'peran' => 'pemohon', 'aktif' => true]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('izin.create')->with('pesan', 'Akun dibuat. Silakan ajukan izin kerja khusus.');
    }
}
