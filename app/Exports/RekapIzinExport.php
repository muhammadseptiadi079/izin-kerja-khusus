<?php

namespace App\Exports;

use App\Models\IzinKerja;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapIzinExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /** @param Collection<int, IzinKerja> $izin */
    public function __construct(private Collection $izin) {}

    public function collection(): Collection
    {
        return $this->izin;
    }

    public function title(): string
    {
        return 'Rekap IKK';
    }

    public function headings(): array
    {
        return ['Nomor', 'Jenis', 'Status', 'Nama', 'NIK', 'Nomor WA', 'Departemen', 'Lokasi', 'Pekerjaan', 'Mulai', 'Selesai', 'Diajukan', 'Disahkan', 'Ditutup'];
    }

    /** @param IzinKerja $i */
    public function map($i): array
    {
        return [
            $i->nomor, $i->labelJenis(), $i->labelStatus(), $i->pemohon->name,
            // NIK dan nomor WA ditulis sebagai teks agar nol di depan tidak hilang di Excel.
            ' '.$i->nik, ' '.$i->nomor_wa,
            $i->departemen, $i->lokasiLengkap(), $i->uraian_pekerjaan,
            $i->mulai_at->format('d/m/Y H:i'), $i->selesai_at->format('d/m/Y H:i'),
            $i->diajukan_at?->format('d/m/Y H:i'), $i->disahkan_at?->format('d/m/Y H:i'), $i->ditutup_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
