<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $izin->nomor }}</title>
    <style>
        @page { margin: 22px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.35; color: #000; }
        h1 { font-size: 15px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        .kop td { vertical-align: middle; }
        .kop { border-bottom: 3px solid #000; margin-bottom: 10px; }
        .status { font-size: 12px; font-weight: bold; border: 2px solid #000; padding: 4px 8px; text-align: center; }
        .isi th, .isi td { border: 1px solid #000; padding: 3px 5px; text-align: left; vertical-align: top; }
        .isi th { background: #eee; width: 26%; }
        .isi { margin-bottom: 10px; }
        .ttd th, .ttd td { border: 1px solid #000; padding: 4px; width: 25%; text-align: left; vertical-align: top; }
        .ttd th { background: #eee; }
        .ttd td { height: 60px; }
        .kecil { font-size: 8px; color: #333; }
        .oranye { color: #c2410c; }
    </style>
</head>
<body>
@php $aturan = $izin->aturan(); @endphp
<table class="kop">
    <tr>
        <td style="width:60px"><img src="{{ public_path('img/logo.png') }}" width="52" height="52"></td>
        <td>
            <h1>IZIN KERJA KHUSUS — {{ strtoupper($izin->labelJenis()) }}</h1>
            <div class="oranye"><em>{{ $aturan['label_en'] ?? '' }}</em></div>
            <div>Nomor: <strong>{{ $izin->nomor ?? 'DRAF' }}</strong></div>
        </td>
        <td style="width:120px"><div class="status">{{ strtoupper($izin->labelStatus()) }}</div></td>
    </tr>
</table>

<table class="isi">
    <tr><th>Pemohon</th><td>{{ $izin->pemohon->name }} {{ $izin->pemohon->jabatan ? '('.$izin->pemohon->jabatan.')' : '' }}</td></tr>
    <tr><th>NIK / Nomor WA</th><td>{{ $izin->nik }} / {{ $izin->nomor_wa }}</td></tr>
    <tr><th>Departemen</th><td>{{ $izin->departemen }}</td></tr>
    <tr><th>Lokasi</th><td>{{ $izin->lokasiLengkap() }}</td></tr>
    <tr><th>Uraian pekerjaan</th><td>{!! nl2br(e($izin->uraian_pekerjaan)) !!}</td></tr>
    <tr><th>Peralatan</th><td>{{ $izin->peralatan ?: '—' }}</td></tr>
    <tr><th>Berlaku</th><td>{{ $izin->mulai_at->translatedFormat('d M Y H:i') }} s.d. {{ $izin->selesai_at->translatedFormat('d M Y H:i') }}</td></tr>
    <tr><th>Pekerja</th><td>{!! nl2br(e($izin->pekerja)) !!}</td></tr>
    <tr><th>Bahaya</th><td>{{ implode('; ', $izin->bahaya ?? []) }}{{ $izin->bahaya_lain ? '; '.$izin->bahaya_lain : '' }}</td></tr>
    <tr><th>Pengendalian</th><td>
        @foreach ($aturan['pengendalian'] ?? [] as $item)
            [{{ in_array($item, $izin->pengendalian ?? []) ? 'X' : ' ' }}] {{ $item }}<br>
        @endforeach
        {!! nl2br(e($izin->pengendalian_tambahan)) !!}
    </td></tr>
    <tr><th>APD</th><td>{{ implode(', ', $izin->apd ?? []) }}</td></tr>
    @if ($izin->butuhUjiGas())
        <tr><th>Uji gas</th><td>
            @foreach (config('izin.uji_gas') as $kunci => $batas)
                {{ $batas['label'] }}: {{ $izin->uji_gas[$kunci] ?? '—' }} {{ $batas['satuan'] }}@if (! $loop->last); @endif
            @endforeach
            <br>Oleh {{ $izin->uji_gas_oleh }}, {{ $izin->uji_gas_at?->translatedFormat('d M Y H:i') }}
        </td></tr>
    @endif
    <tr><th>Dokumen pendukung</th><td>
        @foreach (config('izin.dokumen') as $kunci => $label)
            [{{ $izin->dokumenJenis($kunci) ? 'X' : ' ' }}] {{ $label }}<br>
        @endforeach
    </td></tr>
</table>

@if ($izin->ada_insiden !== null || $izin->kesesuaianWaktu())
    @php $waktu = $izin->kesesuaianWaktu(); @endphp
    <table class="isi">
        <tr><th colspan="2" style="width:auto">EVALUASI PASCA PEKERJAAN</th></tr>
        <tr><th>Jam kerja sebenarnya</th><td>
            {{ $izin->mulai_aktual_at?->translatedFormat('d M Y H:i') ?? '—' }} s.d. {{ $izin->selesai_aktual_at?->translatedFormat('d M Y H:i') ?? '—' }}
        </td></tr>
        <tr><th>Kesesuaian waktu</th><td>
            <strong>{{ $waktu['label'] ?? 'Tidak dilaporkan' }}</strong>
            @foreach ($waktu['temuan'] ?? [] as $t)<br>{{ $t }}@endforeach
        </td></tr>
        <tr><th>Insiden</th><td>
            <strong>{{ $izin->labelInsiden() }}</strong>
            @if ($izin->ada_insiden)
                <br>Kronologi: {!! nl2br(e($izin->uraian_insiden)) !!}
                <br>Tindakan: {!! nl2br(e($izin->tindakan_insiden)) !!}
            @endif
        </td></tr>
        @if ($izin->pemeriksaan_penutupan)
            <tr><th>Kondisi area</th><td>
                @foreach (config('izin.pemeriksaan_penutupan') as $item)
                    [{{ in_array($item, $izin->pemeriksaan_penutupan) ? 'X' : ' ' }}] {{ $item }}<br>
                @endforeach
            </td></tr>
        @endif
        @if ($izin->catatan_penutupan)
            <tr><th>Catatan</th><td>{!! nl2br(e($izin->catatan_penutupan)) !!}</td></tr>
        @endif
    </table>
@endif

@php
    $keputusan = fn (string $status) => $izin->riwayat->first(fn ($r) => $r->aksi === 'setujui' && $r->status_dari === $status);
@endphp
<table class="ttd">
    <tr>
        <th>Pemohon</th>
        @foreach (config('izin.tahap_persetujuan') as $tahap)<th>{{ $tahap['label'] }}</th>@endforeach
    </tr>
    <tr>
        <td>{{ $izin->pemohon->name }}<br><span class="kecil">{{ $izin->diajukan_at?->translatedFormat('d M Y H:i') }}</span></td>
        @foreach (config('izin.tahap_persetujuan') as $status => $tahap)
            @php $r = $keputusan($status); @endphp
            <td>@if ($r) Disetujui<br>{{ $r->user->name }}<br><span class="kecil">{{ $r->created_at->translatedFormat('d M Y H:i') }}</span>@endif</td>
        @endforeach
    </tr>
</table>

<p class="kecil">Izin ini wajib dipasang di lokasi kerja selama pekerjaan berlangsung dan dikembalikan saat penutupan. Dicetak {{ now()->translatedFormat('d M Y H:i') }}.</p>
</body>
</html>
