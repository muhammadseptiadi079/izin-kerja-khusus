<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', ['departemen' => config('izin.departemen')]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => ['required', 'string', 'max:50', 'unique:'.User::class],
            'nomor_wa' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'departemen' => ['required', Rule::in(config('izin.departemen'))],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [], ['name' => 'nama', 'nik' => 'NIK', 'nomor_wa' => 'nomor WA', 'password' => 'kata sandi']);

        // Pendaftaran mandiri selalu sebagai pemohon; peran penyetuju hanya diberikan admin.
        $user = User::create([...$data, 'password' => Hash::make($data['password']), 'peran' => 'pemohon', 'aktif' => true]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('izin.create', absolute: false))->with('pesan', 'Akun dibuat. Silakan ajukan izin kerja khusus.');
    }
}
