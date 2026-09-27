<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="halaman-masuk">
    <div class="panel">
        <h1>Izin Kerja Khusus</h1>
        <p class="muted">Masuk untuk mengajukan atau menyetujui izin kerja berisiko tinggi.</p>
        @if ($errors->any())
            <div class="galat">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="bidang">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="bidang">
                <label for="password">Kata sandi</label>
                <input type="password" id="password" name="password" required>
            </div>
            <label class="centang"><input type="checkbox" name="ingat" value="1"> Ingat saya</label>
            <button class="tombol" type="submit" style="width:100%;justify-content:center;margin-top:.5rem">Masuk</button>
        </form>
    </div>
</div>
</body>
</html>
