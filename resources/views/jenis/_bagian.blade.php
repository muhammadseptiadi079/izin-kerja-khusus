{{-- Satu bagian jenis izin, seperti halaman per jenis di situs IKK sebelumnya. --}}
<section class="bagian-jenis" id="{{ $jenis }}">
    <div class="bagian-gambar">
        @if ($gambar = \App\Support\GambarJenis::url($jenis))
            <img src="{{ $gambar }}" alt="{{ $aturan['label'] }}" loading="lazy">
        @endif
    </div>
    <div class="bagian-teks">
        @include('jenis._judul')
        <p class="sub-jenis">{{ $aturan['deskripsi'] }}</p>
        <a class="tombol tombol-registrasi" href="{{ route('izin.create', ['jenis' => $jenis]) }}">Klik di sini untuk Registrasi</a>
        <p>{{ $aturan['penjelasan'] }}</p>
        <a href="{{ route('jenis.show', $jenis) }}">Lihat bahaya, pengendalian, dan persyaratan →</a>
    </div>
</section>
