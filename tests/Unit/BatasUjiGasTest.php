<?php

use App\Support\PemeriksaIzin;

// Batas aman uji gas menentukan boleh tidaknya pekerjaan dimulai, jadi nilai tepat di batas diuji.
test('nilai uji gas dinilai terhadap batas aman', function (string $gas, float $nilai, bool $aman) {
    expect(PemeriksaIzin::aman($gas, $nilai))->toBe($aman);
})->with([
    'O₂ terlalu rendah' => ['o2', 19.4, false],
    'O₂ tepat batas bawah' => ['o2', 19.5, true],
    'O₂ normal' => ['o2', 20.9, true],
    'O₂ tepat batas atas' => ['o2', 23.5, true],
    'O₂ terlalu tinggi' => ['o2', 23.6, false],
    'LEL tepat 10%' => ['lel', 10, true],
    'LEL di atas 10%' => ['lel', 10.1, false],
    'H₂S tepat 10 ppm' => ['h2s', 10, true],
    'H₂S di atas 10 ppm' => ['h2s', 11, false],
    'CO tepat 25 ppm' => ['co', 25, true],
    'CO di atas 25 ppm' => ['co', 26, false],
]);
