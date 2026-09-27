@extends('layouts.app')
@section('judul', 'Daftar Izin')

@section('isi')
<div class="kepala">
    <div>
        <h1>Daftar Izin</h1>
        <p class="muted">{{ auth()->user()->melihatSemuaIzin() ? 'Semua izin kerja khusus.' : 'Izin yang Anda ajukan.' }}</p>
    </div>
    <a class="tombol" href="{{ route('izin.create') }}">+ Ajukan izin</a>
</div>

<form class="panel filter" method="get">
    <div>
        <label for="cari">Cari</label>
        <input type="text" id="cari" name="cari" value="{{ request('cari') }}" placeholder="Nomor, lokasi, atau pekerjaan">
    </div>
    <div>
        <label for="jenis">Jenis</label>
        <select id="jenis" name="jenis">
            <option value="">Semua jenis</option>
            @foreach (config('izin.jenis') as $kunci => $jenis)
                <option value="{{ $kunci }}" @selected(request('jenis') === $kunci)>{{ $jenis['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach (\App\Models\IzinKerja::STATUS as $kunci => $label)
                <option value="{{ $kunci }}" @selected(request('status') === $kunci)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:0 0 auto" class="baris-tombol">
        <button class="tombol" type="submit">Terapkan</button>
        <a class="tombol sekunder" href="{{ route('izin.index') }}">Atur ulang</a>
    </div>
</form>

<div class="panel">
    @if ($izin->isEmpty())
        <p class="muted">Belum ada izin yang cocok dengan filter ini.</p>
    @else
    <div class="tabel-gulir">
    <table>
        <thead><tr><th>Nomor</th><th>Jenis</th><th>Lokasi & pekerjaan</th><th>Pemohon</th><th>Jadwal</th><th>Status</th></tr></thead>
        <tbody>
        @foreach ($izin as $item)
            <tr>
                <td><a href="{{ route('izin.show', $item) }}">{{ $item->nomor ?? 'Draf #'.$item->id }}</a></td>
                <td>{{ $item->labelJenis() }}</td>
                <td><strong>{{ $item->lokasi }}</strong><div class="small muted">{{ \Illuminate\Support\Str::limit($item->uraian_pekerjaan, 80) }}</div></td>
                <td>{{ $item->pemohon->name }}</td>
                <td class="small">{{ $item->mulai_at->translatedFormat('d M H:i') }}<br>s.d. {{ $item->selesai_at->translatedFormat('d M H:i') }}</td>
                <td>@include('izin._status', ['izin' => $item])</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    <div class="paginasi">
        <span class="small muted">Menampilkan {{ $izin->firstItem() }}–{{ $izin->lastItem() }} dari {{ $izin->total() }}</span>
        <span class="baris-tombol">
            @if ($izin->previousPageUrl())<a class="tombol kecil sekunder" href="{{ $izin->previousPageUrl() }}">‹ Sebelumnya</a>@endif
            @if ($izin->nextPageUrl())<a class="tombol kecil sekunder" href="{{ $izin->nextPageUrl() }}">Berikutnya ›</a>@endif
        </span>
    </div>
    @endif
</div>
@endsection
