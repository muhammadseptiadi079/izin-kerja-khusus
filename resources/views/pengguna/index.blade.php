@extends('layouts.app')
@section('judul', 'Pengguna')

@section('isi')
<div class="kepala">
    <div>
        <h1>Pengguna</h1>
        <p class="muted">Peran menentukan tahap persetujuan yang dapat diputuskan setiap orang.</p>
    </div>
    <a class="tombol" href="{{ route('pengguna.create') }}">+ Tambah pengguna</a>
</div>
<div class="panel tabel-gulir">
    <table>
        <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Jabatan / Departemen</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($pengguna as $orang)
            <tr>
                <td>{{ $orang->name }}</td>
                <td>{{ $orang->email }}</td>
                <td>{{ $orang->labelPeran() }}</td>
                <td>{{ collect([$orang->jabatan, $orang->departemen])->filter()->implode(' · ') ?: '—' }}</td>
                <td>@if ($orang->aktif)<span class="lencana aktif">Aktif</span>@else<span class="lencana">Nonaktif</span>@endif</td>
                <td><a class="tombol kecil sekunder" href="{{ route('pengguna.edit', $orang) }}">Ubah</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
