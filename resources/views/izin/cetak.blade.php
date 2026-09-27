<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $izin->nomor }} · {{ config('app.name') }}</title>
    @include('layouts._ikon')
    <style>
        body { font: 12px/1.4 Arial, sans-serif; color: #000; margin: 24px; }
        h1 { font-size: 18px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #eee; width: 28%; }
        .kepala { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .status { font-size: 14px; font-weight: bold; border: 2px solid #000; padding: 4px 10px; }
        .ttd td { height: 70px; width: 25%; }
        .tidak-dicetak { margin-bottom: 16px; }
        @media print { .tidak-dicetak { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
<div class="tidak-dicetak"><button onclick="window.print()">Cetak</button></div>

<div class="kepala">
    <img src="{{ asset('img/logo.png') }}" alt="Logo" width="64" height="64" style="margin-right:12px">
    <div style="flex:1">
        <h1>IZIN KERJA KHUSUS — {{ strtoupper($izin->labelJenis()) }}</h1>
        <div><em>{{ $izin->aturan()['label_en'] ?? '' }}</em></div>
        <div>Nomor: <strong>{{ $izin->nomor }}</strong></div>
    </div>
    <div class="status">{{ strtoupper($izin->labelStatus()) }}</div>
</div>

<table>
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
        @foreach ($izin->aturan()['pengendalian'] as $item)
            [{{ in_array($item, $izin->pengendalian ?? []) ? 'X' : ' ' }}] {{ $item }}<br>
        @endforeach
        {{ $izin->pengendalian_tambahan }}
    </td></tr>
    <tr><th>Dokumen pendukung</th><td>
        @foreach (config('izin.dokumen') as $kunci => $label)
            [{{ $izin->dokumenJenis($kunci) ? 'X' : ' ' }}] {{ $label }}<br>
        @endforeach
    </td></tr>
    <tr><th>APD</th><td>{{ implode(', ', $izin->apd ?? []) }}</td></tr>
    @if ($izin->butuhUjiGas())
        <tr><th>Uji gas</th><td>
            @foreach (config('izin.uji_gas') as $kunci => $batas)
                {{ $batas['label'] }}: {{ $izin->uji_gas[$kunci] ?? '—' }} {{ $batas['satuan'] }} &nbsp;
            @endforeach
            <br>Oleh {{ $izin->uji_gas_oleh }}, {{ $izin->uji_gas_at?->translatedFormat('d M Y H:i') }}
        </td></tr>
    @endif
</table>

@php
    $keputusan = fn (string $status) => $izin->riwayat->first(fn ($r) => $r->aksi === 'setujui' && $r->status_dari === $status);
@endphp
<table class="ttd">
    <tr>
        <th style="width:25%">Pemohon</th>
        @foreach (config('izin.tahap_persetujuan') as $tahap)<th style="width:25%">{{ $tahap['label'] }}</th>@endforeach
    </tr>
    <tr>
        <td>{{ $izin->pemohon->name }}<br><small>{{ $izin->diajukan_at?->translatedFormat('d M Y H:i') }}</small></td>
        @foreach (config('izin.tahap_persetujuan') as $status => $tahap)
            @php $r = $keputusan($status); @endphp
            <td>@if ($r) Disetujui<br>{{ $r->user->name }}<br><small>{{ $r->created_at->translatedFormat('d M Y H:i') }}</small>@endif</td>
        @endforeach
    </tr>
</table>

<p><small>Izin ini wajib dipasang di lokasi kerja selama pekerjaan berlangsung dan dikembalikan saat penutupan. Dicetak {{ now()->translatedFormat('d M Y H:i') }}.</small></p>
</body>
</html>
