<?php

/*
 * Aturan Izin Kerja Khusus.
 *
 * Setiap jenis izin membawa daftar bahaya yang dipertimbangkan pemohon dan
 * daftar pengendalian yang WAJIB dicentang semuanya sebelum izin bisa
 * diajukan. Jenis yang `uji_gas` = true harus menyertakan hasil uji gas
 * yang berada di dalam batas aman di bawah.
 */

return [

    'jenis' => [
        'kerja_panas' => [
            'label' => 'Kerja Panas',
            'kode' => 'KP',
            'deskripsi' => 'Pengelasan, pemotongan, gerinda, atau pekerjaan lain yang menimbulkan api atau percikan.',
            'durasi_maks_jam' => 12,
            'uji_gas' => true,
            'bahaya' => [
                'Percikan api mengenai bahan mudah terbakar',
                'Uap/gas mudah terbakar di sekitar area',
                'Asap dan fume pengelasan',
                'Luka bakar',
                'Sengatan listrik mesin las',
            ],
            'pengendalian' => [
                'Bahan mudah terbakar dalam radius 11 m dipindahkan atau ditutup selimut api',
                'APAR siap pakai di lokasi',
                'Petugas pengawas api (fire watch) ditunjuk dan berada di lokasi',
                'Fire watch tetap berjaga minimal 30 menit setelah pekerjaan selesai',
                'Kabel dan mesin las diperiksa dalam kondisi baik',
            ],
        ],
        'ruang_terbatas' => [
            'label' => 'Ruang Terbatas',
            'kode' => 'RT',
            'deskripsi' => 'Masuk ke tangki, bejana, saluran, sumur, atau ruang dengan akses keluar-masuk terbatas.',
            'durasi_maks_jam' => 8,
            'uji_gas' => true,
            'bahaya' => [
                'Kekurangan atau kelebihan oksigen',
                'Gas beracun (H2S, CO)',
                'Atmosfer mudah terbakar',
                'Terjebak atau tertimbun material',
                'Sulitnya evakuasi korban',
            ],
            'pengendalian' => [
                'Semua jalur masuk energi dan material diisolasi dan dikunci (LOTO)',
                'Ventilasi paksa terpasang dan berjalan',
                'Uji gas dilakukan sebelum masuk dan dipantau terus-menerus',
                'Petugas jaga (attendant) berada di pintu masuk sepanjang pekerjaan',
                'Peralatan dan tim penyelamatan siap di lokasi',
                'Daftar orang masuk-keluar dicatat',
            ],
        ],
        'ketinggian' => [
            'label' => 'Bekerja di Ketinggian',
            'kode' => 'KT',
            'deskripsi' => 'Pekerjaan pada ketinggian 1,8 m atau lebih dari lantai kerja.',
            'durasi_maks_jam' => 12,
            'uji_gas' => false,
            'bahaya' => [
                'Jatuh dari ketinggian',
                'Benda jatuh menimpa orang di bawah',
                'Perancah atau tangga runtuh',
                'Angin kencang atau cuaca buruk',
            ],
            'pengendalian' => [
                'Full body harness dengan double lanyard dipakai dan dikaitkan 100%',
                'Titik tambat (anchor) mampu menahan beban jatuh',
                'Perancah sudah diinspeksi dan bertanda hijau',
                'Area di bawah diberi barikade dan rambu',
                'Alat dan material diikat agar tidak jatuh',
            ],
        ],
        'isolasi_energi' => [
            'label' => 'Isolasi Energi / Listrik',
            'kode' => 'IE',
            'deskripsi' => 'Pekerjaan pada instalasi listrik, hidrolik, pneumatik, atau mesin yang harus diisolasi (LOTO).',
            'durasi_maks_jam' => 12,
            'uji_gas' => false,
            'bahaya' => [
                'Sengatan listrik',
                'Busur api listrik (arc flash)',
                'Mesin bergerak tiba-tiba',
                'Energi tersimpan (tekanan, pegas, kapasitor)',
            ],
            'pengendalian' => [
                'Sumber energi diidentifikasi dan diisolasi',
                'Gembok dan tag LOTO terpasang oleh setiap pekerja',
                'Uji nol energi (try-out) dilakukan sebelum bekerja',
                'Energi tersimpan dilepaskan',
                'Pekerja kompeten dan berwenang untuk pekerjaan listrik',
            ],
        ],
        'penggalian' => [
            'label' => 'Penggalian',
            'kode' => 'PG',
            'deskripsi' => 'Penggalian atau pengeboran tanah yang dapat mengenai utilitas bawah tanah.',
            'durasi_maks_jam' => 12,
            'uji_gas' => false,
            'bahaya' => [
                'Dinding galian longsor',
                'Mengenai kabel listrik atau pipa bawah tanah',
                'Orang atau alat jatuh ke galian',
                'Genangan air di galian',
            ],
            'pengendalian' => [
                'Gambar utilitas bawah tanah diperiksa dan dideteksi',
                'Dinding galian dilandaikan atau diberi penahan',
                'Material galian ditempatkan minimal 1 m dari tepi',
                'Barikade dan akses keluar-masuk galian tersedia',
            ],
        ],
        'angkat_berat' => [
            'label' => 'Pengangkatan Kritis',
            'kode' => 'AK',
            'deskripsi' => 'Pengangkatan dengan crane di atas 75% kapasitas, dua crane, atau di atas area berpenghuni/berenergi.',
            'durasi_maks_jam' => 12,
            'uji_gas' => false,
            'bahaya' => [
                'Beban jatuh',
                'Crane terguling',
                'Boom mengenai jaringan listrik',
                'Terjepit beban',
            ],
            'pengendalian' => [
                'Rencana pengangkatan (lifting plan) disetujui',
                'Operator dan rigger bersertifikat',
                'Alat angkat dan sling diinspeksi',
                'Area radius ayun diberi barikade',
                'Kecepatan angin di bawah batas operasi crane',
            ],
        ],
    ],

    // Batas aman uji gas. Nilai di luar rentang membuat izin tidak dapat diajukan.
    'uji_gas' => [
        'o2' => ['label' => 'Oksigen (O₂)', 'satuan' => '%', 'min' => 19.5, 'maks' => 23.5],
        'lel' => ['label' => 'Gas mudah terbakar (LEL)', 'satuan' => '%', 'min' => 0, 'maks' => 10],
        'h2s' => ['label' => 'Hidrogen sulfida (H₂S)', 'satuan' => 'ppm', 'min' => 0, 'maks' => 10],
        'co' => ['label' => 'Karbon monoksida (CO)', 'satuan' => 'ppm', 'min' => 0, 'maks' => 25],
    ],

    'apd' => [
        'Helm keselamatan',
        'Sepatu keselamatan',
        'Kacamata keselamatan',
        'Sarung tangan',
        'Rompi reflektif',
        'Pelindung telinga',
        'Masker / respirator',
        'Topeng las',
        'Full body harness',
        'Pakaian tahan api',
    ],

    // Urutan persetujuan. Setiap tahap diputuskan oleh pengguna dengan peran itu.
    'tahap_persetujuan' => [
        'menunggu_pengawas' => ['peran' => 'pengawas', 'label' => 'Pengawas Area', 'berikutnya' => 'menunggu_hse'],
        'menunggu_hse' => ['peran' => 'hse', 'label' => 'HSE', 'berikutnya' => 'menunggu_manajer'],
        'menunggu_manajer' => ['peran' => 'manajer', 'label' => 'Manajer / Penanggung Jawab', 'berikutnya' => 'aktif'],
    ],

];
