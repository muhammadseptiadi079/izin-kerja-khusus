<?php

namespace App\Http\Controllers;

use App\Models\IzinKerja;
use App\Support\DaftarTindakan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TindakanController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $tindakan = new DaftarTindakan($request->user());
        $ringkas = fn ($izin) => $izin->map(fn (IzinKerja $i) => $i->ringkas())->values();

        return Inertia::render('Tindakan', [
            'menungguKeputusan' => $ringkas($tindakan->menungguKeputusan()),
            'perluDiperbaiki' => $ringkas($tindakan->perluDiperbaiki()),
            'lewatWaktu' => $ringkas($tindakan->lewatWaktu()),
            'draf' => $ringkas($tindakan->draf()),
        ]);
    }
}
