@extends('layouts.app')
@section('judul', $izin->exists ? 'Ubah Izin' : 'Ajukan Izin')

@php
    $aturan = $izin->aturan();
    $formatWaktu = fn ($nilai) => $nilai ? \Illuminate\Support\Carbon::parse($nilai)->format('Y-m-d\TH:i') : '';
    $bahaya = old('bahaya', $izin->bahaya ?? []);
    $pengendalian = old('pengendalian', $izin->pengendalian ?? []);
    $apd = old('apd', $izin->apd ?? []);
    $ujiGas = old('uji_gas', $izin->uji_gas ?? []);
@endphp

@section('isi')
<div class="kepala">
    <div>
        <h1>{{ $aturan['label'] }}</h1>
        <p class="muted">{{ $aturan['deskripsi'] }} Durasi maksimal {{ $aturan['durasi_maks_jam'] }} jam.</p>
    </div>
    @if ($izin->exists)
        <a class="tombol sekunder" href="{{ route('izin.show', $izin) }}">Kembali ke izin</a>
    @endif
</div>

@if ($izin->status === 'ditolak')
    @php $penolakan = $izin->riwayat()->where('aksi', 'tolak')->latest('id')->first(); @endphp
    @if ($penolakan)
        <div class="galat"><strong>Ditolak oleh {{ $penolakan->user->name }}:</strong> {{ $penolakan->catatan }}</div>
    @endif
@endif

<form method="post" action="{{ $izin->exists ? route('izin.update', $izin) : route('izin.store') }}">
    @csrf
    @if ($izin->exists) @method('put') @endif
    <input type="hidden" name="jenis" value="{{ $izin->jenis }}">

    <div class="panel">
        <h2>1. Pekerjaan</h2>
        <div class="grid grid-2">
            <div class="bidang">
                <label for="lokasi">Lokasi kerja</label>
                <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi', $izin->lokasi) }}" required placeholder="Contoh: Workshop Pit 3, Tangki T-201">
            </div>
            <div class="bidang">
                <label for="peralatan">Peralatan yang digunakan</label>
                <input type="text" id="peralatan" name="peralatan" value="{{ old('peralatan', $izin->peralatan) }}" placeholder="Mesin las, gerinda, scaffolding, dll.">
            </div>
        </div>
        <div class="bidang">
            <label for="uraian_pekerjaan">Uraian pekerjaan</label>
            <textarea id="uraian_pekerjaan" name="uraian_pekerjaan" required>{{ old('uraian_pekerjaan', $izin->uraian_pekerjaan) }}</textarea>
        </div>
        <div class="grid grid-3">
            <div class="bidang">
                <label for="mulai_at">Mulai</label>
                <input type="datetime-local" id="mulai_at" name="mulai_at" value="{{ $formatWaktu(old('mulai_at', $izin->mulai_at)) }}" required>
            </div>
            <div class="bidang">
                <label for="selesai_at">Selesai</label>
                <input type="datetime-local" id="selesai_at" name="selesai_at" value="{{ $formatWaktu(old('selesai_at', $izin->selesai_at)) }}" required>
            </div>
        </div>
        <div class="bidang">
            <label for="pekerja">Nama pekerja</label>
            <textarea id="pekerja" name="pekerja" required placeholder="Satu nama per baris">{{ old('pekerja', $izin->pekerja) }}</textarea>
            <div class="bantuan">Tulis semua orang yang akan bekerja di bawah izin ini, satu per baris.</div>
        </div>
    </div>

    <div class="panel">
        <h2>2. Identifikasi bahaya</h2>
        @foreach ($aturan['bahaya'] as $item)
            <label class="centang"><input type="checkbox" name="bahaya[]" value="{{ $item }}" @checked(in_array($item, $bahaya))> {{ $item }}</label>
        @endforeach
        <div class="bidang" style="margin-top:.75rem">
            <label for="bahaya_lain">Bahaya lain</label>
            <textarea id="bahaya_lain" name="bahaya_lain">{{ old('bahaya_lain', $izin->bahaya_lain) }}</textarea>
        </div>
    </div>

    <div class="panel">
        <h2>3. Pengendalian wajib</h2>
        <p class="muted small">Semua butir harus sudah dipastikan di lapangan sebelum izin dapat diajukan.</p>
        @foreach ($aturan['pengendalian'] as $item)
            <label class="centang"><input type="checkbox" name="pengendalian[]" value="{{ $item }}" @checked(in_array($item, $pengendalian))> {{ $item }}</label>
        @endforeach
        <div class="bidang" style="margin-top:.75rem">
            <label for="pengendalian_tambahan">Pengendalian tambahan</label>
            <textarea id="pengendalian_tambahan" name="pengendalian_tambahan">{{ old('pengendalian_tambahan', $izin->pengendalian_tambahan) }}</textarea>
        </div>
    </div>

    <div class="panel">
        <h2>4. Alat pelindung diri (APD)</h2>
        <div class="grid grid-3">
            @foreach (config('izin.apd') as $item)
                <label class="centang"><input type="checkbox" name="apd[]" value="{{ $item }}" @checked(in_array($item, $apd))> {{ $item }}</label>
            @endforeach
        </div>
    </div>

    @if ($aturan['uji_gas'])
    <div class="panel">
        <h2>5. Uji gas</h2>
        <p class="muted small">Izin tidak dapat diajukan bila salah satu hasil di luar batas aman.</p>
        <div class="grid grid-4">
            @foreach (config('izin.uji_gas') as $kunci => $batas)
                <div class="bidang">
                    <label for="gas_{{ $kunci }}">{{ $batas['label'] }}</label>
                    <input type="number" step="0.1" min="0" id="gas_{{ $kunci }}" name="uji_gas[{{ $kunci }}]" value="{{ $ujiGas[$kunci] ?? '' }}">
                    <div class="bantuan">Aman: {{ $batas['min'] }}–{{ $batas['maks'] }} {{ $batas['satuan'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="grid grid-2">
            <div class="bidang">
                <label for="uji_gas_oleh">Diuji oleh</label>
                <input type="text" id="uji_gas_oleh" name="uji_gas_oleh" value="{{ old('uji_gas_oleh', $izin->uji_gas_oleh) }}">
            </div>
            <div class="bidang">
                <label for="uji_gas_at">Waktu pengujian</label>
                <input type="datetime-local" id="uji_gas_at" name="uji_gas_at" value="{{ $formatWaktu(old('uji_gas_at', $izin->uji_gas_at)) }}">
            </div>
        </div>
    </div>
    @endif

    <div class="panel baris-tombol">
        <button class="tombol" type="submit" name="ajukan" value="1">Simpan & ajukan</button>
        <button class="tombol sekunder" type="submit" name="ajukan" value="0">Simpan sebagai draf</button>
        <span class="muted small">Draf bisa dilengkapi nanti. Izin yang diajukan diteruskan ke Pengawas Area.</span>
    </div>
</form>
@endsection
