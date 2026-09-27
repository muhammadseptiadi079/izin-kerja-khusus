<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Dasbor') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="topbar">
    <a class="merek" href="{{ route('beranda') }}"><span>IKK</span> Izin Kerja Khusus</a>
    <nav>
        <a href="{{ route('beranda') }}" @class(['aktif' => request()->routeIs('beranda')])>Home</a>
        <a href="{{ route('izin.create') }}" @class(['aktif' => request()->routeIs('izin.create', 'daftar')])>Registrasi</a>
        <a href="{{ route('monitoring') }}" @class(['aktif' => request()->routeIs('monitoring')])>Monitoring & Evaluasi</a>
        @auth
            <a href="{{ route('dasbor') }}" @class(['aktif' => request()->routeIs('dasbor')])>Dasbor</a>
            <a href="{{ route('izin.index') }}" @class(['aktif' => request()->routeIs('izin.index', 'izin.show', 'izin.edit')])>Daftar Izin</a>
            @if (auth()->user()->adalah('admin'))
                <a href="{{ route('pengguna.index') }}" @class(['aktif' => request()->routeIs('pengguna.*')])>Pengguna</a>
            @endif
        @endauth
    </nav>
    <div class="akun">
        @auth
            <span>{{ auth()->user()->name }} · {{ auth()->user()->labelPeran() }}</span>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        @else
            <a class="tombol kecil" href="{{ route('login') }}">Masuk</a>
        @endauth
    </div>
</header>
<main>
    @if (session('pesan'))
        <div class="pesan">{{ session('pesan') }}</div>
    @endif
    @if ($errors->any())
        <div class="galat">
            <strong>Periksa kembali:</strong>
            <ul>
                @foreach ($errors->all() as $galat)
                    <li>{{ $galat }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('isi')
</main>
</body>
</html>
