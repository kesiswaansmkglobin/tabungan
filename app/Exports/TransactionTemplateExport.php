<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['Tanggal', 'NIS', 'Nama', 'Jenis', 'Jumlah', 'Keterangan'];
    }

    public function array(): array
    {
        return [
            ['2024-01-15', '123456', 'Azzam', 'setor', '50000', 'Tabungan rutin'],
            ['2024-01-20', '123456', 'Azzam', 'tarik', '20000', 'Beli buku'],
            ['2024-02-01', '', 'Budi', 'setor', '100000', ''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
