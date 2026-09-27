@extends('layouts.app')
@section('judul', $aturan['label'])

@section('isi')
<p class="small"><a href="{{ route('beranda') }}#jenis">← Semua jenis izin</a></p>

<div class="panel bagian-tunggal">
    @include('jenis._judul')
    <p class="sub-jenis">{{ $aturan['deskripsi'] }}</p>
    <a class="tombol tombol-registrasi" href="{{ route('izin.create', ['jenis' => $jenis]) }}">Klik di sini untuk Registrasi</a>
    @if ($gambar = \App\Support\GambarJenis::url($jenis))
        <img class="gambar-besar" src="{{ $gambar }}" alt="{{ $aturan['label'] }}">
    @endif
    <p class="penjelasan">{{ $aturan['penjelasan'] }}</p>
</div>

<div class="grid grid-2">
    <div class="panel">
        <h2>Bahaya yang harus diidentifikasi</h2>
        <ul class="daftar">@foreach ($aturan['bahaya'] as $item)<li>{{ $item }}</li>@endforeach</ul>
    </div>
    <div class="panel">
        <h2>Pengendalian wajib</h2>
        <p class="small muted">Semua butir harus dipastikan di lapangan sebelum izin bisa diajukan.</p>
        <ul class="daftar">@foreach ($aturan['pengendalian'] as $item)<li>{{ $item }}</li>@endforeach</ul>
    </div>
    <div class="panel">
        <h2>Persyaratan pengajuan</h2>
        <ul class="daftar">
            @foreach (config('izin.dokumen') as $label)<li>Unggah {{ $label }}</li>@endforeach
            <li>Durasi izin maksimal {{ $aturan['durasi_maks_jam'] }} jam. Pekerjaan lebih lama diajukan per shift.</li>
            @if ($aturan['uji_gas'])
                <li>Hasil uji gas dalam batas aman:
                    @foreach (config('izin.uji_gas') as $batas){{ $batas['label'] }} {{ $batas['min'] }}–{{ $batas['maks'] }} {{ $batas['satuan'] }}@if (! $loop->last), @endif @endforeach
                </li>
            @endif
        </ul>
    </div>
    <div class="panel">
        <h2>Alur persetujuan</h2>
        <ol class="daftar">
            @foreach (config('izin.tahap_persetujuan') as $tahap)<li>{{ $tahap['label'] }}</li>@endforeach
            <li>Izin aktif, pekerjaan boleh dimulai</li>
            <li>Penutupan oleh Pengawas Area</li>
        </ol>
    </div>
</div>

<div class="panel baris-tombol">
    <a class="tombol tombol-registrasi" href="{{ route('izin.create', ['jenis' => $jenis]) }}">Klik di sini untuk Registrasi</a>
</div>
@endsection
