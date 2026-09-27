@extends('layouts.app')
@section('judul', $pengguna->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('isi')
<div class="kepala"><h1>{{ $pengguna->exists ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1></div>
<form class="panel" method="post" action="{{ $pengguna->exists ? route('pengguna.update', $pengguna) : route('pengguna.store') }}" style="max-width:640px">
    @csrf
    @if ($pengguna->exists) @method('put') @endif
    <div class="bidang"><label for="name">Nama</label><input type="text" id="name" name="name" value="{{ old('name', $pengguna->name) }}" required></div>
    <div class="bidang"><label for="email">Email</label><input type="email" id="email" name="email" value="{{ old('email', $pengguna->email) }}" required></div>
    <div class="bidang">
        <label for="peran">Peran</label>
        <select id="peran" name="peran">
            @foreach (\App\Models\User::PERAN as $kunci => $label)
                <option value="{{ $kunci }}" @selected(old('peran', $pengguna->peran) === $kunci)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-2">
        <div class="bidang"><label for="jabatan">Jabatan</label><input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $pengguna->jabatan) }}"></div>
        <div class="bidang"><label for="departemen">Departemen</label><input type="text" id="departemen" name="departemen" value="{{ old('departemen', $pengguna->departemen) }}"></div>
    </div>
    <div class="bidang">
        <label for="password">Kata sandi</label>
        <input type="password" id="password" name="password" autocomplete="new-password" @required(! $pengguna->exists)>
        @if ($pengguna->exists)<div class="bantuan">Kosongkan bila tidak diganti.</div>@endif
    </div>
    <label class="centang"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $pengguna->aktif))> Akun aktif (dapat masuk)</label>
    <div class="baris-tombol" style="margin-top:1rem">
        <button class="tombol" type="submit">Simpan</button>
        <a class="tombol sekunder" href="{{ route('pengguna.index') }}">Batal</a>
    </div>
</form>
@endsection
