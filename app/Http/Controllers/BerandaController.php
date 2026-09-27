<?php

namespace App\Http\Controllers;

use App\Support\Katalog;
use Inertia\Inertia;
use Inertia\Response;

class BerandaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Beranda', [
            'jenis' => Katalog::jenis(),
            'dokumen' => config('izin.dokumen'),
            'tahap' => Katalog::umum()['tahap'],
        ]);
    }

    public function jenis(string $jenis): Response
    {
        $aturan = collect(Katalog::jenis())->firstWhere('kunci', $jenis) ?? abort(404);

        return Inertia::render('Jenis/Show', [
            'jenis' => $aturan,
            ...collect(Katalog::umum())->only(['dokumen', 'uji_gas', 'tahap'])->all(),
        ]);
    }
}
