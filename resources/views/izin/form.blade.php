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

<form method="post" enctype="multipart/form-data" action="{{ $izin->exists ? route('izin.update', $izin) : route('izin.store') }}">
    @csrf
    @if ($izin->exists) @method('put') @endif
    <input type="hidden" name="jenis" value="{{ $izin->jenis }}">

    <div class="panel">
        <h2>1. Data pemohon</h2>
        <div class="grid grid-2">
            <div class="bidang">
                <label for="nama">Nama / <em>Your name</em></label>
                <input type="text" id="nama" value="{{ $izin->pemohon->name ?? auth()->user()->name }}" readonly>
                <div class="bantuan">Diambil dari akun Anda.</div>
            </div>
            <div class="bidang">
                <label for="nik">NIK / <em>Your ID</em></label>
                <input type="text" id="nik" name="nik" value="{{ old('nik', $izin->nik) }}" required>
            </div>
            <div class="bidang">
                <label for="nomor_wa">Nomor WA / <em>WhatsApp</em></label>
                <input type="text" id="nomor_wa" name="nomor_wa" value="{{ old('nomor_wa', $izin->nomor_wa) }}" required inputmode="tel" placeholder="08xxxxxxxxxx">
                <div class="bantuan">Dipakai untuk menghubungi Anda terkait izin ini.</div>
            </div>
            <div class="bidang">
                <label for="departemen">Departemen pelapor / <em>Your department</em></label>
                <select id="departemen" name="departemen" required>
                    <option value="">Pilih</option>
                    @foreach (config('izin.departemen') as $d)<option @selected(old('departemen', $izin->departemen) === $d)>{{ $d }}</option>@endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2>2. Pekerjaan</h2>
        <div class="grid grid-2">
            <div class="bidang">
                <label for="lokasi">Lokasi / <em>Location</em></label>
                <select id="lokasi" name="lokasi" required>
                    <option value="">Pilih</option>
                    @foreach (config('izin.lokasi') as $l)<option @selected(old('lokasi', $izin->lokasi) === $l)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="bidang">
                <label for="lokasi_detail">Detail lokasi</label>
                <input type="text" id="lokasi_detail" name="lokasi_detail" value="{{ old('lokasi_detail', $izin->lokasi_detail) }}" placeholder="Contoh: Front loading blok B, tangki T-201">
            </div>
        </div>
        <div class="bidang">
            <label for="peralatan">Peralatan yang digunakan</label>
            <input type="text" id="peralatan" name="peralatan" value="{{ old('peralatan', $izin->peralatan) }}" placeholder="Mesin las, chainsaw, crane 50 ton, dll.">
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
        <h2>3. Identifikasi bahaya</h2>
        @foreach ($aturan['bahaya'] as $item)
            <label class="centang"><input type="checkbox" name="bahaya[]" value="{{ $item }}" @checked(in_array($item, $bahaya))> {{ $item }}</label>
        @endforeach
        <div class="bidang" style="margin-top:.75rem">
            <label for="bahaya_lain">Bahaya lain</label>
            <textarea id="bahaya_lain" name="bahaya_lain">{{ old('bahaya_lain', $izin->bahaya_lain) }}</textarea>
        </div>
    </div>

    <div class="panel">
        <h2>4. Pengendalian wajib</h2>
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
        <h2>5. Alat pelindung diri (APD)</h2>
        <div class="grid grid-3">
            @foreach (config('izin.apd') as $item)
                <label class="centang"><input type="checkbox" name="apd[]" value="{{ $item }}" @checked(in_array($item, $apd))> {{ $item }}</label>
            @endforeach
        </div>
    </div>

    @if ($aturan['uji_gas'])
    <div class="panel">
        <h2>6. Uji gas</h2>
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

    <div class="panel">
        <h2>Dokumen pendukung</h2>
        <p class="muted small">Unggah 1 berkas PDF atau Word untuk masing-masing, maksimal {{ config('izin.dokumen_maks_kb') / 1024 }} MB. Ketiganya wajib sebelum izin bisa diajukan.</p>
        @foreach (config('izin.dokumen') as $kunci => $label)
            @php $ada = $izin->exists ? $izin->dokumen->firstWhere('jenis', $kunci) : null; @endphp
            <div class="dokumen-baris">
                <div>
                    <strong>{{ $label }}</strong>
                    <div class="small {{ $ada ? '' : 'muted' }}">
                        @if ($ada)
                            ✅ <a href="{{ route('izin.dokumen', [$izin, $kunci]) }}">{{ $ada->nama_asli }}</a> ({{ $ada->ukuranTerbaca() }}). Pilih berkas baru untuk mengganti.
                        @else
                            Belum diunggah
                        @endif
                    </div>
                    @error('dokumen.'.$kunci)<div class="teks-galat">{{ $message }}</div>@enderror
                </div>
                <input type="file" name="dokumen[{{ $kunci }}]" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
            </div>
        @endforeach
    </div>

    <div class="panel baris-tombol">
        <button class="tombol" type="submit" name="ajukan" value="1">Simpan & ajukan</button>
        <button class="tombol sekunder" type="submit" name="ajukan" value="0">Simpan sebagai draf</button>
        <span class="muted small">Draf bisa dilengkapi nanti. Izin yang diajukan diteruskan ke Pengawas Area.</span>
    </div>
</form>
@endsection
