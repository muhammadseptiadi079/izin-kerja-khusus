@extends('layouts.app')
@section('judul', $izin->nomor ?? 'Draf Izin')

@php
    $tahapan = config('izin.tahap_persetujuan');
    $urutan = [...array_keys($tahapan), 'aktif', 'menunggu_penutupan', 'selesai'];
    $posisi = array_search($izin->status, $urutan, true);
    $labelAksi = [
        'setujui' => ['Setujui', 'hijau'],
        'tolak' => ['Tolak', 'merah'],
        'batalkan' => ['Batalkan izin', 'sekunder'],
        'ajukan_penutupan' => ['Pekerjaan selesai, ajukan penutupan', ''],
        'tutup' => ['Konfirmasi area aman & tutup izin', 'hijau'],
        'hentikan' => ['Hentikan pekerjaan', 'merah'],
    ];
    $butuhCatatan = ['tolak', 'hentikan', 'ajukan_penutupan'];
@endphp

@section('isi')
<div class="kepala">
    <div>
        <h1>{{ $izin->nomor ?? 'Draf #'.$izin->id }} · {{ $izin->labelJenis() }}</h1>
        <p>@include('izin._status')</p>
    </div>
    <div class="baris-tombol">
        @if (in_array('ajukan', $aksi))
            <a class="tombol" href="{{ route('izin.edit', $izin) }}">Ubah & ajukan</a>
        @endif
        @if ($izin->nomor)
            <a class="tombol sekunder" href="{{ route('izin.cetak', $izin) }}" target="_blank">Cetak izin</a>
        @endif
    </div>
</div>

@if ($posisi !== false)
<div class="panel">
    <h2>Tahapan</h2>
    <ol class="tahapan">
        @foreach ($tahapan as $kunci => $tahap)
            <li @class(['lewat' => $posisi > array_search($kunci, $urutan), 'kini' => $izin->status === $kunci])>{{ $tahap['label'] }}</li>
        @endforeach
        <li @class(['lewat' => $posisi > array_search('aktif', $urutan), 'kini' => $izin->status === 'aktif'])>Pekerjaan berjalan</li>
        <li @class(['lewat' => $izin->status === 'selesai', 'kini' => $izin->status === 'menunggu_penutupan'])>Penutupan</li>
    </ol>
</div>
@endif

@if ($izin->lewatWaktu())
    <div class="galat">Waktu izin sudah berakhir pada {{ $izin->selesai_at->translatedFormat('d M Y H:i') }}. Hentikan pekerjaan dan ajukan penutupan, atau ajukan izin baru.</div>
@endif

@php $aksiTombol = array_values(array_intersect(array_keys($labelAksi), $aksi)); @endphp
@if ($aksiTombol)
<div class="panel">
    <h2>Tindakan</h2>
    <form method="post" action="{{ route('izin.aksi', $izin) }}">
        @csrf
        <div class="bidang">
            <label for="catatan">Catatan</label>
            <textarea id="catatan" name="catatan" placeholder="Wajib diisi untuk penolakan, penghentian, dan penutupan">{{ old('catatan') }}</textarea>
        </div>
        <div class="baris-tombol">
            @foreach ($aksiTombol as $kunci)
                <button class="tombol {{ $labelAksi[$kunci][1] }}" type="submit" name="aksi" value="{{ $kunci }}"
                    @if (in_array($kunci, ['hentikan', 'batalkan'])) onclick="return confirm('Yakin {{ strtolower($labelAksi[$kunci][0]) }}?')" @endif>
                    {{ $labelAksi[$kunci][0] }}
                </button>
            @endforeach
        </div>
    </form>
</div>
@endif

<div class="grid grid-2">
    <div class="panel">
        <h2>Rincian pekerjaan</h2>
        <dl class="rincian">
            <dt>Pemohon</dt><dd>{{ $izin->pemohon->name }}{{ $izin->pemohon->jabatan ? ' · '.$izin->pemohon->jabatan : '' }}</dd>
            <dt>NIK</dt><dd>{{ $izin->nik }}</dd>
            <dt>Nomor WA</dt><dd><a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $izin->nomor_wa)) }}" target="_blank" rel="noopener">{{ $izin->nomor_wa }}</a></dd>
            <dt>Departemen</dt><dd>{{ $izin->departemen }}</dd>
            <dt>Lokasi</dt><dd>{{ $izin->lokasiLengkap() }}</dd>
            <dt>Pekerjaan</dt><dd>{{ $izin->uraian_pekerjaan }}</dd>
            <dt>Peralatan</dt><dd>{{ $izin->peralatan ?: '—' }}</dd>
            <dt>Mulai</dt><dd>{{ $izin->mulai_at->translatedFormat('l, d M Y H:i') }}</dd>
            <dt>Selesai</dt><dd>{{ $izin->selesai_at->translatedFormat('l, d M Y H:i') }}</dd>
            <dt>Pekerja</dt><dd>{{ $izin->pekerja }}</dd>
            @if ($izin->catatan_penutupan)
                <dt>Catatan penutupan</dt><dd>{{ $izin->catatan_penutupan }}</dd>
            @endif
        </dl>
    </div>

    <div class="panel">
        <h2>Bahaya & pengendalian</h2>
        <h3>Bahaya teridentifikasi</h3>
        <ul class="daftar">
            @forelse ($izin->bahaya ?? [] as $item)<li>{{ $item }}</li>@empty<li class="muted">Belum dipilih</li>@endforelse
            @if ($izin->bahaya_lain)<li>{{ $izin->bahaya_lain }}</li>@endif
        </ul>
        <h3>Pengendalian</h3>
        <ul class="daftar">
            @foreach ($izin->aturan()['pengendalian'] ?? [] as $item)
                <li>{!! in_array($item, $izin->pengendalian ?? []) ? '✅' : '⬜' !!} {{ $item }}</li>
            @endforeach
            @if ($izin->pengendalian_tambahan)<li>➕ {{ $izin->pengendalian_tambahan }}</li>@endif
        </ul>
        <h3>APD</h3>
        <p>{{ implode(', ', $izin->apd ?? []) ?: '—' }}</p>

        @if ($izin->butuhUjiGas())
            <h3>Uji gas</h3>
            <table>
                @foreach (config('izin.uji_gas') as $kunci => $batas)
                    @php $nilai = $izin->uji_gas[$kunci] ?? null; @endphp
                    <tr>
                        <td>{{ $batas['label'] }}</td>
                        <td>
                            @if ($nilai === null || $nilai === '')
                                <span class="muted">—</span>
                            @else
                                <span class="{{ \App\Support\PemeriksaIzin::aman($kunci, (float) $nilai) ? 'gas-aman' : 'gas-bahaya' }}">{{ $nilai }} {{ $batas['satuan'] }}</span>
                            @endif
                        </td>
                        <td class="small muted">{{ $batas['min'] }}–{{ $batas['maks'] }} {{ $batas['satuan'] }}</td>
                    </tr>
                @endforeach
            </table>
            <p class="small muted">Diuji oleh {{ $izin->uji_gas_oleh ?: '—' }}{{ $izin->uji_gas_at ? ', '.$izin->uji_gas_at->translatedFormat('d M Y H:i') : '' }}</p>
        @endif
    </div>
</div>

<div class="panel">
    <h2>Dokumen pendukung</h2>
    @foreach (config('izin.dokumen') as $kunci => $label)
        @php $dok = $izin->dokumenJenis($kunci); @endphp
        <div class="dokumen-baris">
            <strong>{{ $label }}</strong>
            @if ($dok)
                <a class="tombol kecil sekunder" href="{{ route('izin.dokumen', [$izin, $kunci]) }}">Unduh {{ $dok->nama_asli }} ({{ $dok->ukuranTerbaca() }})</a>
            @else
                <span class="lencana ditolak">Belum diunggah</span>
            @endif
        </div>
    @endforeach
</div>

<div class="panel">
    <h2>Riwayat</h2>
    <ul class="linimasa">
        @foreach ($izin->riwayat as $catatan)
            <li>
                <strong>{{ $catatan->labelAksi() }}</strong> — {{ $catatan->user->name }} ({{ $catatan->user->labelPeran() }})
                <div class="small muted">{{ $catatan->created_at->translatedFormat('d M Y H:i') }} · {{ \App\Models\IzinKerja::STATUS[$catatan->status_ke] ?? $catatan->status_ke }}</div>
                @if ($catatan->catatan)<div class="catatan">{{ $catatan->catatan }}</div>@endif
            </li>
        @endforeach
    </ul>
</div>
@endsection
