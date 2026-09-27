<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Meminta Gemini menyarankan isian identifikasi bahaya untuk sebuah pekerjaan.
 *
 * Saran dibatasi pada pilihan yang ada di config/izin.php, ditambah teks
 * bebas untuk bahaya lain dan pengendalian tambahan. Pengendalian wajib
 * TIDAK pernah disarankan untuk dicentang: itu harus dipastikan orang di lapangan.
 */
class AsistenGemini
{
    public function tersedia(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * @return array{bahaya: list<string>, bahaya_lain: string, pengendalian_tambahan: string, apd: list<string>, catatan: string}
     */
    public function sarankan(string $jenis, string $uraian, ?string $lokasi, ?string $peralatan): array
    {
        $aturan = config('izin.jenis.'.$jenis);
        $apd = config('izin.apd');

        $prompt = implode("\n", [
            'Anda ahli K3 pertambangan di Indonesia (SMKP Minerba). Bantu pemohon mengisi Izin Kerja Khusus.',
            'Jenis izin: '.$aturan['label'].' ('.$aturan['label_en'].').',
            'Uraian pekerjaan: '.$uraian,
            'Lokasi: '.($lokasi ?: '-'),
            'Peralatan: '.($peralatan ?: '-'),
            '',
            'Pilih bahaya yang relevan HANYA dari daftar ini: '.json_encode($aturan['bahaya'], JSON_UNESCAPED_UNICODE),
            'Pilih APD yang wajib HANYA dari daftar ini: '.json_encode($apd, JSON_UNESCAPED_UNICODE),
            'Pengendalian wajib berikut SUDAH ada di formulir, jangan diulang: '.json_encode($aturan['pengendalian'], JSON_UNESCAPED_UNICODE),
            'Tulis bahaya_lain dan pengendalian_tambahan yang spesifik untuk pekerjaan ini (singkat, poin dipisah baris baru, bahasa Indonesia).',
            'Isi catatan dengan satu kalimat peringatan terpenting.',
        ]);

        try {
            $respons = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(30)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent', [
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'bahaya' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                                'bahaya_lain' => ['type' => 'STRING'],
                                'pengendalian_tambahan' => ['type' => 'STRING'],
                                'apd' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                                'catatan' => ['type' => 'STRING'],
                            ],
                            'required' => ['bahaya', 'bahaya_lain', 'pengendalian_tambahan', 'apd', 'catatan'],
                        ],
                    ],
                ])
                ->throw();
        } catch (ConnectionException|RequestException $e) {
            throw new RuntimeException('Asisten AI tidak dapat dihubungi. Coba lagi nanti.', previous: $e);
        }

        $hasil = json_decode((string) $respons->json('candidates.0.content.parts.0.text'), true);

        if (! is_array($hasil)) {
            throw new RuntimeException('Asisten AI memberi jawaban yang tidak dapat dibaca. Coba lagi.');
        }

        // Buang pilihan yang tidak ada di daftar agar formulir tetap konsisten.
        return [
            'bahaya' => array_values(array_intersect($aturan['bahaya'], (array) ($hasil['bahaya'] ?? []))),
            'bahaya_lain' => trim((string) ($hasil['bahaya_lain'] ?? '')),
            'pengendalian_tambahan' => trim((string) ($hasil['pengendalian_tambahan'] ?? '')),
            'apd' => array_values(array_intersect($apd, (array) ($hasil['apd'] ?? []))),
            'catatan' => trim((string) ($hasil['catatan'] ?? '')),
        ];
    }
}
