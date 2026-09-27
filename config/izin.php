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
        'ketinggian' => [
            'label' => 'Bekerja di Ketinggian',
            'label_en' => 'Working at Height',
            'kode' => 'KT',
            'deskripsi' => 'Pekerjaan pada ketinggian 1,8 m atau lebih dari lantai kerja.',
            'penjelasan' => 'Bekerja di ketinggian adalah pekerjaan apa pun yang dilakukan pada permukaan dengan perbedaan ketinggian, di mana pekerja dapat jatuh dan cedera serius atau meninggal dunia. Risiko ini meliputi jatuh dari permukaan kerja, tertimpa benda yang jatuh dari atas, atau terjepit di ruang terbatas. Untuk bekerja dengan aman, diperlukan penerapan standar keselamatan dan kesehatan kerja (K3) yang ketat, termasuk pelatihan khusus, penggunaan alat pelindung diri (APD) seperti harness dan helm, serta perawatan dan pemeriksaan rutin peralatan.',
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
        'ruang_terbatas' => [
            'label' => 'Bekerja di Ruang Terbatas',
            'label_en' => 'Confined Spaces',
            'kode' => 'RT',
            'deskripsi' => 'Masuk ke tangki, bejana, saluran, sumur, atau ruang dengan akses keluar-masuk terbatas.',
            'penjelasan' => 'Bekerja di ruang terbatas atau confined space adalah pekerjaan yang akses masuknya terbatas dan dapat memiliki gas beracun serta kadar oksigen yang rendah, sehingga sangat berisiko karena potensi kekurangan oksigen, keberadaan gas beracun dan mudah terbakar, suhu ekstrem, serta kesulitan komunikasi dan evakuasi. Untuk memastikan keselamatan, perusahaan harus melakukan identifikasi dan evaluasi bahaya, menyediakan program keselamatan dengan pelatihan kerja dan prosedur penyelamatan darurat, serta memasang rambu peringatan di pintu masuk. Pekerja harus menggunakan APD yang sesuai, mendapatkan pelatihan lengkap, dan bekerja di bawah pengawasan ketat dengan tim pendukung di luar ruang terbatas.',
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
        'pengangkatan' => [
            'label' => 'Pengangkatan di Atas 1 Ton',
            'label_en' => 'Lifting Above 1 Ton',
            'kode' => 'PA',
            'deskripsi' => 'Pengangkatan beban lebih dari 1 ton dengan crane, hoist, atau alat angkat lainnya.',
            'penjelasan' => 'Bekerja dengan pengangkatan di atas 1 ton memerlukan peralatan khusus seperti hoist crane atau overhead crane, bukan pengangkatan manual oleh manusia, karena manusia tidak mampu mengangkat beban sebesar itu secara aman dan efisien. Peralatan ini menyediakan sistem mekanis yang kuat dan aman untuk memindahkan beban berat, meningkatkan efisiensi, serta memiliki sistem rem otomatis untuk mencegah beban jatuh.',
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
                'Alat angkat dan sling diinspeksi dan layak pakai',
                'Area radius ayun diberi barikade',
                'Kecepatan angin di bawah batas operasi crane',
            ],
        ],
        'kerja_panas' => [
            'label' => 'Pengelasan di Luar Workshop',
            'label_en' => 'Welding Outside the Workshop (Hot Work)',
            'kode' => 'KP',
            'deskripsi' => 'Pengelasan, pemotongan, gerinda, atau kerja panas lain yang dilakukan di luar workshop.',
            'penjelasan' => 'Izin kerja panas (hot work permit) adalah sistem izin formal yang diperlukan sebelum memulai pekerjaan yang menghasilkan sumber panas, percikan api, atau nyala api, seperti pengelasan atau pemotongan logam, untuk mencegah kebakaran dan potensi ledakan di tempat kerja. Izin ini berfungsi sebagai daftar periksa keselamatan yang memastikan semua tindakan pencegahan telah diambil, termasuk mengidentifikasi dan mengendalikan bahaya, menyiapkan alat pemadam kebakaran, dan menunjuk petugas pengawas kebakaran.',
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
        'penebangan_pohon' => [
            'label' => 'Penebangan Pohon',
            'label_en' => 'Land Clearing',
            'kode' => 'LC',
            'deskripsi' => 'Penebangan pohon dan pembersihan lahan secara manual atau dengan alat berat.',
            'penjelasan' => 'Penebangan pohon atau land clearing adalah pekerjaan membersihkan lahan dari pohon dan vegetasi sebelum kegiatan penambangan, pembuatan jalan, atau pembangunan fasilitas. Bahaya utamanya adalah tertimpa pohon atau dahan yang tumbang, luka akibat chainsaw, alat berat terguling di lereng, serta gigitan binatang berbisa. Pekerjaan ini harus direncanakan dengan menentukan arah rebah pohon, mengosongkan zona bahaya, memastikan operator chainsaw dan alat berat kompeten, serta menyiapkan P3K di lokasi.',
            'durasi_maks_jam' => 12,
            'uji_gas' => false,
            'bahaya' => [
                'Tertimpa pohon atau dahan tumbang',
                'Terkena chainsaw',
                'Alat berat terguling di lereng',
                'Binatang berbisa (ular, lebah)',
                'Pohon mengenai jaringan listrik',
            ],
            'pengendalian' => [
                'Arah rebah pohon ditentukan dan jalur penyelamatan diri disiapkan',
                'Zona bahaya minimal 2 kali tinggi pohon dikosongkan dari orang',
                'Operator chainsaw kompeten dan memakai chaps/pelindung kaki',
                'Jaringan listrik di sekitar sudah diperiksa atau dimatikan',
                'Kotak P3K dan penanganan gigitan binatang tersedia',
            ],
        ],
        'dekat_air' => [
            'label' => 'Bekerja Dekat Air atau Lumpur',
            'label_en' => 'Working Near Water',
            'kode' => 'DA',
            'deskripsi' => 'Pekerjaan di tepi atau di atas sump, kolam pengendapan, sungai, atau area berlumpur.',
            'penjelasan' => 'Bekerja dekat air atau lumpur adalah pekerjaan di tepi atau di atas sump, kolam pengendapan, sungai, rawa, atau area berlumpur, di mana pekerja dan unit berisiko tenggelam, terjebak lumpur, atau tergelincir akibat tanah tepi yang longsor. Untuk bekerja dengan aman, semua pekerja wajib memakai pelampung (life jacket), alat penyelamat seperti ring buoy dan tali harus tersedia, tepi air diberi tanggul dan rambu, kestabilan tanah diperiksa sebelum unit mendekat, dan pekerjaan tidak boleh dilakukan sendirian.',
            'durasi_maks_jam' => 12,
            'uji_gas' => false,
            'bahaya' => [
                'Tenggelam',
                'Terjebak lumpur',
                'Tanah tepi longsor',
                'Alat atau unit tergelincir ke air',
                'Arus air deras',
            ],
            'pengendalian' => [
                'Pelampung (life jacket) dipakai oleh semua pekerja',
                'Ring buoy dan tali penyelamat tersedia di lokasi',
                'Tepi air diberi tanggul/pembatas dan rambu',
                'Kestabilan tanah tepi diperiksa sebelum unit mendekat',
                'Pekerjaan tidak dilakukan sendirian (minimal berdua)',
            ],
        ],
    ],

    /*
     * Pilihan lokasi dan departemen. Ganti dengan daftar di site Anda.
     */
    'lokasi' => [
        'Pit 1',
        'Pit 2',
        'Pit 3',
        'Disposal',
        'Hauling Road',
        'Port / Jetty',
        'Workshop',
        'Mess & Office',
        'Sump / Settling Pond',
        'Lainnya',
    ],

    'departemen' => [
        'Produksi',
        'Plant / Maintenance',
        'Engineering',
        'HSE',
        'Logistik',
        'HRGA',
        'Kontraktor',
    ],

    /*
     * Dokumen pendukung yang wajib diunggah sebelum izin bisa diajukan.
     */
    'dokumen' => [
        'sop' => 'SOP / IK / Standar Parameter',
        'fit_to_work' => 'Fit To Work dari Dokter / Klinik',
        'jsea' => 'Job Safety Environmental Analysis (JSEA)',
    ],
    'dokumen_maks_kb' => 10240,
    // 'local' untuk VPS; 's3' untuk Vercel (Supabase Storage), karena disk Vercel tidak permanen.
    'dokumen_disk' => env('DOKUMEN_DISK', 'local'),
    'dokumen_ekstensi' => ['pdf', 'doc', 'docx'],

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
        'Pelampung (life jacket)',
        'Chaps / pelindung kaki chainsaw',
    ],

    /*
     * Evaluasi pasca pekerjaan, diisi pemohon saat mengajukan penutupan
     * (dan oleh penyetuju saat menghentikan pekerjaan).
     */
    'insiden' => [
        'nyaris_celaka' => 'Nyaris celaka (near miss)',
        'p3k' => 'Cedera ringan / P3K',
        'cedera_berat' => 'Cedera berat / hilang hari kerja',
        'kerusakan_alat' => 'Kerusakan alat / properti',
        'kebakaran' => 'Kebakaran / ledakan',
        'lingkungan' => 'Pencemaran lingkungan / tumpahan',
    ],

    'pemeriksaan_penutupan' => [
        'Semua pekerja sudah keluar dari area kerja',
        'Area kerja bersih dan aman dari sisa material',
        'Peralatan dan alat bantu sudah dirapikan atau dikembalikan',
        'Isolasi, LOTO, dan barikade sudah dilepas sesuai prosedur',
    ],

    // Selisih waktu yang masih dianggap sesuai jadwal.
    'toleransi_waktu_menit' => 15,

    // Urutan persetujuan. Setiap tahap diputuskan oleh pengguna dengan peran itu.
    'tahap_persetujuan' => [
        'menunggu_pengawas' => ['peran' => 'pengawas', 'label' => 'Pengawas Area', 'berikutnya' => 'menunggu_hse'],
        'menunggu_hse' => ['peran' => 'hse', 'label' => 'HSE', 'berikutnya' => 'menunggu_manajer'],
        'menunggu_manajer' => ['peran' => 'manajer', 'label' => 'Manajer / Penanggung Jawab', 'berikutnya' => 'aktif'],
    ],

];
