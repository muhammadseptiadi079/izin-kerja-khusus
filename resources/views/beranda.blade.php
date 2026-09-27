@extends('layouts.app')
@section('judul', 'Home')

@section('isi')
<section class="hero">
    <p class="hero-kecil">Sistem Manajemen Keselamatan Pertambangan</p>
    <h1>Pengajuan & Registrasi Izin Kerja Khusus</h1>
    <p>Ajukan izin kerja berisiko tinggi secara online, lampirkan dokumen pendukung, dan pantau persetujuannya sampai pekerjaan selesai.</p>
    <div class="baris-tombol">
        <a class="tombol" href="{{ route('izin.create') }}">Registrasi izin</a>
        <a class="tombol sekunder" href="{{ route('monitoring') }}">Monitoring & Evaluasi</a>
        @guest<a class="tombol sekunder" href="{{ route('daftar') }}">Buat akun pekerja</a>@endguest
    </div>
</section>

<div class="panel">
    <h2>Apa itu Izin Kerja Khusus?</h2>
    <p>Dalam <strong>SMKP (Sistem Manajemen Keselamatan Pertambangan)</strong>, <strong>Izin Kerja Khusus (IKK)</strong> adalah <strong>izin yang diberikan kepada pekerja atau pihak tertentu untuk melaksanakan pekerjaan yang memiliki potensi bahaya tinggi</strong>. Pekerjaan tersebut hanya boleh dilakukan setelah melalui proses identifikasi bahaya, pengendalian risiko, serta verifikasi kondisi aman di lapangan.</p>
</div>

<h2>Jenis izin kerja khusus</h2>
<div class="grid grid-3 pilih-jenis" style="margin-bottom:1.25rem">
    @foreach (config('izin.jenis') as $kunci => $jenis)
        <a href="{{ route('izin.create', ['jenis' => $kunci]) }}">
            <span class="kode">{{ $loop->iteration }}</span>
            <h2 style="margin-bottom:.1rem">{{ $jenis['label'] }}</h2>
            <p class="small" style="margin:0 0 .4rem;color:var(--brand);font-style:italic">{{ $jenis['label_en'] }}</p>
            <p class="muted small">{{ $jenis['deskripsi'] }}</p>
        </a>
    @endforeach
</div>

<div class="grid grid-2">
    <div class="panel">
        <h2>Dokumen yang wajib dilampirkan</h2>
        <ul class="daftar">
            @foreach (config('izin.dokumen') as $label)
                <li>{{ $label }}</li>
            @endforeach
        </ul>
        <p class="small muted">Format PDF atau dokumen Word, maksimal {{ config('izin.dokumen_maks_kb') / 1024 }} MB per berkas.</p>
    </div>
    <div class="panel">
        <h2>Alur persetujuan</h2>
        <ol class="daftar">
            <li>Pekerja mengisi registrasi, identifikasi bahaya, pengendalian, dan dokumen.</li>
            @foreach (config('izin.tahap_persetujuan') as $tahap)
                <li>Diperiksa dan disetujui {{ $tahap['label'] }}.</li>
            @endforeach
            <li>Izin aktif: pekerjaan boleh dimulai. Izin dicetak dan dipasang di lokasi.</li>
            <li>Pekerjaan selesai, Pengawas memastikan area aman lalu izin ditutup.</li>
        </ol>
    </div>
</div>
@endsection
