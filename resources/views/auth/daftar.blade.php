@extends('layouts.app')
@section('judul', 'Buat Akun')

@section('isi')
<div class="kepala">
    <div>
        <h1>Buat akun pekerja</h1>
        <p class="muted">Akun dipakai untuk mengajukan izin dan memantau status persetujuannya. Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>.</p>
    </div>
</div>
<form class="panel" method="post" action="{{ route('daftar') }}" style="max-width:680px">
    @csrf
    <div class="bidang"><label for="name">Nama / <em>Your name</em></label><input type="text" id="name" name="name" value="{{ old('name') }}" required></div>
    <div class="grid grid-2">
        <div class="bidang"><label for="nik">NIK / <em>Your ID</em></label><input type="text" id="nik" name="nik" value="{{ old('nik') }}" required></div>
        <div class="bidang"><label for="nomor_wa">Nomor WA / <em>WhatsApp</em></label><input type="text" id="nomor_wa" name="nomor_wa" value="{{ old('nomor_wa') }}" required placeholder="08xxxxxxxxxx" inputmode="tel"></div>
    </div>
    <div class="grid grid-2">
        <div class="bidang">
            <label for="departemen">Departemen / <em>Department</em></label>
            <select id="departemen" name="departemen" required>
                <option value="">Pilih</option>
                @foreach (config('izin.departemen') as $d)<option @selected(old('departemen') === $d)>{{ $d }}</option>@endforeach
            </select>
        </div>
        <div class="bidang"><label for="jabatan">Jabatan</label><input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan') }}"></div>
    </div>
    <div class="bidang"><label for="email">Email</label><input type="email" id="email" name="email" value="{{ old('email') }}" required></div>
    <div class="grid grid-2">
        <div class="bidang"><label for="password">Kata sandi</label><input type="password" id="password" name="password" required autocomplete="new-password"><div class="bantuan">Minimal 8 karakter.</div></div>
        <div class="bidang"><label for="password_confirmation">Ulangi kata sandi</label><input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"></div>
    </div>
    <button class="tombol" type="submit">Buat akun</button>
</form>
@endsection
