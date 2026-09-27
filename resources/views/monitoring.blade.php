@extends('layouts.app')
@section('judul', 'Monitoring & Evaluasi')

@php
    $maks = fn ($data) => max(1, $data->max() ?? 1);
@endphp

@section('isi')
<div class="kepala">
    <div>
        <h1>Monitoring & Evaluasi</h1>
        <p class="muted">Rekap izin kerja khusus berdasarkan jadwal mulai, {{ $dari->translatedFormat('d M Y') }} – {{ $sampai->translatedFormat('d M Y') }}. Draf dan izin yang dibatalkan tidak dihitung.</p>
    </div>
    <a class="tombol sekunder" href="{{ route('monitoring.ekspor', request()->query()) }}">Unduh CSV (Excel)</a>
</div>

<form class="panel filter" method="get">
    <div><label for="dari">Dari</label><input type="date" id="dari" name="dari" value="{{ $dari->format('Y-m-d') }}"></div>
    <div><label for="sampai">Sampai</label><input type="date" id="sampai" name="sampai" value="{{ $sampai->format('Y-m-d') }}"></div>
    <div>
        <label for="jenis">Jenis</label>
        <select id="jenis" name="jenis"><option value="">Semua</option>
            @foreach (config('izin.jenis') as $k => $j)<option value="{{ $k }}" @selected(request('jenis') === $k)>{{ $j['label'] }}</option>@endforeach
        </select>
    </div>
    <div>
        <label for="lokasi">Lokasi</label>
        <select id="lokasi" name="lokasi"><option value="">Semua</option>
            @foreach (config('izin.lokasi') as $l)<option @selected(request('lokasi') === $l)>{{ $l }}</option>@endforeach
        </select>
    </div>
    <div>
        <label for="departemen">Departemen</label>
        <select id="departemen" name="departemen"><option value="">Semua</option>
            @foreach (config('izin.departemen') as $d)<option @selected(request('departemen') === $d)>{{ $d }}</option>@endforeach
        </select>
    </div>
    <div style="flex:0 0 auto" class="baris-tombol">
        <button class="tombol" type="submit">Terapkan</button>
        <a class="tombol sekunder" href="{{ route('monitoring') }}">Bulan ini</a>
    </div>
</form>

<div class="grid grid-4" style="margin-bottom:1.25rem">
    <div class="statistik"><div class="angka">{{ $evaluasi['diajukan'] }}</div><div class="muted">Izin diajukan</div></div>
    <div class="statistik"><div class="angka">{{ $evaluasi['disetujui'] }}</div><div class="muted">Disetujui & aktif</div></div>
    <div class="statistik"><div class="angka">{{ $evaluasi['selesai'] }}</div><div class="muted">Selesai & ditutup</div></div>
    <div class="statistik"><div class="angka">{{ $evaluasi['rata_jam_persetujuan'] ?? '—' }}</div><div class="muted">Rata-rata jam sampai disetujui</div></div>
    <div class="statistik"><div class="angka">{{ $evaluasi['ditolak_sekali'] }}</div><div class="muted">Pernah ditolak (perlu perbaikan)</div></div>
    <div @class(['statistik', 'bahaya' => $evaluasi['dihentikan'] > 0])><div class="angka">{{ $evaluasi['dihentikan'] }}</div><div class="muted">Dihentikan (stop work)</div></div>
    <div @class(['statistik', 'bahaya' => $evaluasi['lewat_waktu'] > 0])><div class="angka">{{ $evaluasi['lewat_waktu'] }}</div><div class="muted">Aktif lewat waktu, belum ditutup</div></div>
    <div class="statistik"><div class="angka">{{ $evaluasi['diajukan'] ? round($evaluasi['selesai'] / $evaluasi['diajukan'] * 100) : 0 }}%</div><div class="muted">Tingkat penutupan</div></div>
</div>

@if ($evaluasi['total'] === 0)
    <div class="panel"><p class="muted">Belum ada izin pada periode dan filter ini.</p></div>
@else
<div class="grid grid-2">
    @foreach ([
        ['Per jenis izin', $perJenis, fn ($k) => config('izin.jenis.'.$k.'.label', $k)],
        ['Per lokasi', $perLokasi, fn ($k) => $k],
        ['Per departemen', $perDepartemen, fn ($k) => $k],
        ['Per status', $perStatus, fn ($k) => \App\Models\IzinKerja::STATUS[$k] ?? $k],
    ] as [$judul, $data, $label])
        <div class="panel">
            <h2>{{ $judul }}</h2>
            <table class="rekap">
                @foreach ($data as $kunci => $jumlah)
                    <tr>
                        <td>{{ $label($kunci) }}</td>
                        <td class="batang-sel"><span class="batang" style="width:{{ $jumlah / $maks($data) * 100 }}%"></span></td>
                        <td class="angka-sel">{{ $jumlah }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach
</div>

<div class="panel">
    <h2>Izin per hari mulai</h2>
    <table class="rekap">
        @foreach ($perHari as $hari => $jumlah)
            <tr>
                <td style="width:140px">{{ \Illuminate\Support\Carbon::parse($hari)->translatedFormat('D, d M') }}</td>
                <td class="batang-sel"><span class="batang" style="width:{{ $jumlah / $maks($perHari) * 100 }}%"></span></td>
                <td class="angka-sel">{{ $jumlah }}</td>
            </tr>
        @endforeach
    </table>
</div>
@endif
@endsection
