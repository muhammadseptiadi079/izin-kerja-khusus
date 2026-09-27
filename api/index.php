<?php

/*
 * Titik masuk Vercel (runtime vercel-php).
 *
 * Disk Vercel hanya bisa ditulis di /tmp, jadi storage Laravel dipindah ke
 * sana. Dokumen unggahan disimpan di Supabase Storage (DOKUMEN_DISK=s3),
 * bukan di /tmp, karena /tmp hilang setiap kali fungsi dijalankan ulang.
 */

$storage = '/tmp/storage';

foreach (['app/private', 'framework/cache/data', 'framework/sessions', 'framework/views', 'fonts', 'logs'] as $folder) {
    if (! is_dir($storage.'/'.$folder)) {
        mkdir($storage.'/'.$folder, 0755, true);
    }
}

$_ENV['LARAVEL_STORAGE_PATH'] = $_SERVER['LARAVEL_STORAGE_PATH'] = $storage;

require __DIR__.'/../public/index.php';
