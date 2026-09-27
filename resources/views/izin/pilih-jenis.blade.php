@extends('layouts.app')
@section('judul', 'Ajukan Izin')

@section('isi')
<div class="kepala">
    <div>
        <h1>Registrasi Izin Kerja Khusus</h1>
        <p class="muted">Pilih jenis izin kerja khusus (<em>work permit system</em>) untuk pekerjaan yang akan dilakukan.</p>
    </div>
</div>

<div class="grid grid-3 pilih-jenis">
    @foreach (config('izin.jenis') as $kunci => $jenis)
        <a href="{{ route('izin.create', ['jenis' => $kunci]) }}">
            <span class="kode">{{ $jenis['kode'] }}</span>
            <h2 style="margin-bottom:.1rem">{{ $jenis['label'] }}</h2>
            <p class="small" style="margin:0 0 .4rem;color:var(--brand);font-style:italic">{{ $jenis['label_en'] }}</p>
            <p class="muted small">{{ $jenis['deskripsi'] }}</p>
            <p class="small">Maks. {{ $jenis['durasi_maks_jam'] }} jam @if ($jenis['uji_gas']) · wajib uji gas @endif</p>
        </a>
    @endforeach
</div>
@endsection
