<?php

namespace App\Http\Controllers;

class JenisIzinController extends Controller
{
    public function show(string $jenis)
    {
        $aturan = config('izin.jenis.'.$jenis) ?? abort(404);

        return view('jenis.show', compact('jenis', 'aturan'));
    }
}
