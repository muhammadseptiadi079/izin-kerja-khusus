@extends('layouts.app')
@section('judul', 'Ajukan Izin')

@section('isi')
<div class="kepala">
    <div>
        <h1>Ajukan Izin Kerja Khusus</h1>
        <p class="muted">Pilih jenis pekerjaan berisiko tinggi yang akan dilakukan.</p>
    </div>
</div>

<div class="grid grid-3 pilih-jenis">
    @foreach (config('izin.jenis') as $kunci => $jenis)
        <a href="{{ route('izin.create', ['jenis' => $kunci]) }}">
            <span class="kode">{{ $jenis['kode'] }}</span>
            <h2>{{ $jenis['label'] }}</h2>
            <p class="muted small">{{ $jenis['deskripsi'] }}</p>
            <p class="small">Maks. {{ $jenis['durasi_maks_jam'] }} jam @if ($jenis['uji_gas']) · wajib uji gas @endif</p>
        </a>
    @endforeach
</div>
@endsection
