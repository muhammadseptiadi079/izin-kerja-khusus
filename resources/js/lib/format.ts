const zona = 'Asia/Jakarta';

/** 27 Sep 2026 14.00 */
export function tanggalJam(iso: string | null | undefined): string {
    if (!iso) return '—';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: zona,
    }).format(new Date(iso));
}

/** Minggu, 27 Sep 2026 14.00 */
export function tanggalPanjang(iso: string | null | undefined): string {
    if (!iso) return '—';
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: zona,
    }).format(new Date(iso));
}

/** Min, 27 Sep (untuk tanggal Y-m-d tanpa jam) */
export function hariPendek(ymd: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
    }).format(new Date(ymd + 'T00:00:00'));
}

/** Nilai untuk <input type="datetime-local"> dalam zona Asia/Jakarta. */
export function keInputWaktu(iso: string | null | undefined): string {
    if (!iso) return '';
    const bagian = new Intl.DateTimeFormat('sv-SE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: zona,
    }).format(new Date(iso));
    return bagian.replace(' ', 'T');
}

/** Tautan wa.me dari nomor lokal 08xx. */
export function tautanWa(nomor: string): string {
    return 'https://wa.me/' + nomor.replace(/\D/g, '').replace(/^0/, '62');
}
