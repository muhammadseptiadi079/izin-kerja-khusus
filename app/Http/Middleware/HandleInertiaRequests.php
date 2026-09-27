<?php

namespace App\Http\Middleware;

use App\Support\DaftarTindakan;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'peran' => $user->peran,
                    'label_peran' => $user->labelPeran(),
                    'nik' => $user->nik,
                    'nomor_wa' => $user->nomor_wa,
                    'jabatan' => $user->jabatan,
                    'departemen' => $user->departemen,
                ] : null,
            ],
            'pesan' => fn () => $request->session()->get('pesan'),
            // Angka merah di menu Tindakan.
            'jumlahTindakan' => fn () => $user ? (new DaftarTindakan($user))->jumlah() : 0,
        ];
    }
}
