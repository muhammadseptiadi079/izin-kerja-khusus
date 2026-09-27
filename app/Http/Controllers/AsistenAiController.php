<?php

namespace App\Http\Controllers;

use App\Services\AsistenGemini;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class AsistenAiController extends Controller
{
    public function __invoke(Request $request, AsistenGemini $asisten): JsonResponse
    {
        abort_unless($asisten->tersedia(), 404);

        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(config('izin.jenis')))],
            'uraian_pekerjaan' => ['required', 'string', 'min:10', 'max:5000'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'peralatan' => ['nullable', 'string', 'max:2000'],
        ], ['uraian_pekerjaan.min' => 'Tulis uraian pekerjaan dulu (minimal 10 karakter) agar AI bisa memberi saran.']);

        try {
            return response()->json($asisten->sarankan($data['jenis'], $data['uraian_pekerjaan'], $data['lokasi'] ?? null, $data['peralatan'] ?? null));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}
