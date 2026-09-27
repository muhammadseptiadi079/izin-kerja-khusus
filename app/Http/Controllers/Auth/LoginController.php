<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt([...$kredensial, 'aktif' => true], $request->boolean('ingat'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah, atau akun dinonaktifkan.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dasbor'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
