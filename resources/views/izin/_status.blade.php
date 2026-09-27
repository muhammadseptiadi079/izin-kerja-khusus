<span class="lencana {{ $izin->status }}">{{ $izin->labelStatus() }}</span>
@if ($izin->lewatWaktu())
    <span class="lencana lewat">Lewat waktu</span>
@endif
