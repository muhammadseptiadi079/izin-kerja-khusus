@extends('layouts.app')

@section('isi')
<div class="kepala">
    <div>
        <h1>Dasbor</h1>
        <p class="muted">Selamat datang, {{ auth()->user()->name }}.</p>
    </div>
    <a class="tombol" href="{{ route('izin.create') }}">+ Ajukan izin</a>
</div>

<div class="grid grid-4" style="margin-bottom:1.25rem">
    <div class="statistik"><div class="angka">{{ $ringkasan['aktif'] }}</div><div class="muted">Izin aktif</div></div>
    <div @class(['statistik', 'bahaya' => $ringkasan['lewat_waktu'] > 0])><div class="angka">{{ $ringkasan['lewat_waktu'] }}</div><div class="muted">Lewat waktu, belum ditutup</div></div>
    <div class="statistik"><div class="angka">{{ $ringkasan['menunggu'] }}</div><div class="muted">Menunggu persetujuan</div></div>
    <div class="statistik"><div class="angka">{{ $ringkasan['selesai_bulan_ini'] }}</div><div class="muted">Selesai bulan ini</div></div>
</div>

@if ($perluTindakan->isNotEmpty())
<div class="panel">
    <h2>Perlu tindakan Anda ({{ $perluTindakan->count() }})</h2>
    <div class="tabel-gulir">
    <table>
        <thead><tr><th>Nomor</th><th>Jenis</th><th>Lokasi</th><th>Pemohon</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($perluTindakan as $izin)
            <tr>
                <td>{{ $izin->nomor }}</td>
                <td>{{ $izin->labelJenis() }}</td>
                <td>{{ $izin->lokasi }}</td>
                <td>{{ $izin->pemohon->name }}</td>
                <td>@include('izin._status')</td>
                <td><a class="tombol kecil" href="{{ route('izin.show', $izin) }}">Tinjau</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

@if ($izinSaya->isNotEmpty())
<div class="panel">
    <h2>Draf & izin ditolak milik Anda</h2>
    <div class="tabel-gulir">
    <table>
        <thead><tr><th>Jenis</th><th>Lokasi</th><th>Status</th><th>Diperbarui</th><th></th></tr></thead>
        <tbody>
        @foreach ($izinSaya as $izin)
            <tr>
                <td>{{ $izin->labelJenis() }}</td>
                <td>{{ $izin->lokasi }}</td>
                <td>@include('izin._status')</td>
                <td>{{ $izin->updated_at->translatedFormat('d M Y H:i') }}</td>
                <td><a class="tombol kecil sekunder" href="{{ route('izin.edit', $izin) }}">Lanjutkan</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

<div class="panel">
    <h2>Izin aktif di lapangan</h2>
    @if ($aktif->isEmpty())
        <p class="muted">Tidak ada izin yang sedang berlaku.</p>
    @else
    <div class="tabel-gulir">
    <table>
        <thead><tr><th>Nomor</th><th>Jenis</th><th>Lokasi</th><th>Pemohon</th><th>Berlaku sampai</th><th>Status</th></tr></thead>
        <tbody>
        @foreach ($aktif as $izin)
            <tr>
                <td><a href="{{ route('izin.show', $izin) }}">{{ $izin->nomor }}</a></td>
                <td>{{ $izin->labelJenis() }}</td>
                <td>{{ $izin->lokasi }}</td>
                <td>{{ $izin->pemohon->name }}</td>
                <td>{{ $izin->selesai_at->translatedFormat('d M Y H:i') }}</td>
                <td>@include('izin._status')</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
