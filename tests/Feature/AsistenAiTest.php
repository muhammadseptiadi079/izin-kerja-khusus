<?php

use Illuminate\Support\Facades\Http;

function jawabanGemini(array $isi): array
{
    return ['candidates' => [['content' => ['parts' => [['text' => json_encode($isi)]]]]]];
}

test('tombol AI tersembunyi dan endpoint mati bila kunci Gemini kosong', function () {
    config(['services.gemini.key' => null]);

    $this->actingAs(akun())->postJson(route('izin.saran-ai'), [
        'jenis' => 'kerja_panas',
        'uraian_pekerjaan' => 'Pengelasan bucket excavator',
    ])->assertNotFound();
});

test('saran AI dibatasi pada pilihan yang ada di formulir', function () {
    config(['services.gemini.key' => 'kunci-uji']);
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(jawabanGemini([
            'bahaya' => ['Luka bakar', 'Bahaya karangan AI'],
            'bahaya_lain' => 'Percikan mengenai selang hidrolik',
            'pengendalian_tambahan' => 'Tutup selang hidrolik dengan selimut api',
            'apd' => ['Topeng las', 'Jetpack'],
            'catatan' => 'Pastikan tidak ada oli bocor.',
        ])),
    ]);

    $this->actingAs(akun())->postJson(route('izin.saran-ai'), [
        'jenis' => 'kerja_panas',
        'uraian_pekerjaan' => 'Pengelasan bucket excavator di area pit',
        'lokasi' => 'Pit 3',
    ])->assertOk()->assertExactJson([
        'bahaya' => ['Luka bakar'],
        'bahaya_lain' => 'Percikan mengenai selang hidrolik',
        'pengendalian_tambahan' => 'Tutup selang hidrolik dengan selimut api',
        'apd' => ['Topeng las'],
        'catatan' => 'Pastikan tidak ada oli bocor.',
    ]);

    Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'kunci-uji')
        && str_contains($request->url(), 'gemini-2.5-flash:generateContent')
        && str_contains($request['contents'][0]['parts'][0]['text'], 'Pengelasan bucket excavator'));
});

test('kegagalan Gemini dilaporkan dengan pesan yang jelas', function () {
    config(['services.gemini.key' => 'kunci-uji']);
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response('error', 500)]);

    $this->actingAs(akun())->postJson(route('izin.saran-ai'), [
        'jenis' => 'ketinggian',
        'uraian_pekerjaan' => 'Pasang atap workshop',
    ])->assertStatus(502)->assertJson(['message' => 'Asisten AI tidak dapat dihubungi. Coba lagi nanti.']);
});

test('uraian pekerjaan wajib diisi sebelum meminta saran', function () {
    config(['services.gemini.key' => 'kunci-uji']);

    $this->actingAs(akun())->postJson(route('izin.saran-ai'), ['jenis' => 'ketinggian', 'uraian_pekerjaan' => 'x'])
        ->assertUnprocessable()->assertJsonValidationErrors('uraian_pekerjaan');
});
