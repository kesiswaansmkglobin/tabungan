<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private array $filters = [],
        private ?array $allowedClassIds = null,
    ) {}

    public function query(): Builder
    {
        $query = Transaction::with('student:id,nis,name,class_id', 'student.class:id,name', 'createdBy:id,name');

        if (! empty($this->filters['student_id'])) {
            $query->where('student_id', $this->filters['student_id']);
        }
        if (! empty($this->filters['class_id']) && $this->allowedClassIds === null) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $this->filters['class_id']));
        }
        if (! empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (! empty($this->filters['date_from'])) {
            $query->where('transaction_date', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->where('transaction_date', '<=', $this->filters['date_to'].' 23:59:59');
        }
        if ($this->allowedClassIds !== null) {
            $query->whereHas('student', fn ($q) => $q->whereIn('class_id', $this->allowedClassIds));
        }

        return $query->latest('transaction_date');
    }

    public function headings(): array
    {
        return ['Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jenis', 'Jumlah', 'Saldo Akhir', 'Keterangan', 'Petugas'];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date->format('d/m/Y'),
            $transaction->student->nis,
            $transaction->student->name,
            $transaction->student->class->name ?? '-',
            $transaction->type === 'setor' ? 'Setoran' : 'Penarikan',
            $transaction->amount,
            $transaction->balance_after,
            $transaction->note ?? '-',
            $transaction->createdBy->name,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}
