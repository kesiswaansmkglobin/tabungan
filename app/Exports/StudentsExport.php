<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private array $filters = []
    ) {}

    public function query(): Builder
    {
        $query = Student::with('class:id,name');

        if (! empty($this->filters['class_id'])) {
            $query->where('class_id', $this->filters['class_id']);
        }

        if (! empty($this->filters['class_ids'])) {
            $query->whereIn('class_id', $this->filters['class_ids']);
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return ['NIS', 'Nama', 'Kelas', 'Saldo'];
    }

    public function map($student): array
    {
        return [
            $student->nis,
            $student->name,
            $student->class?->name ?? '-',
            $student->balance,
        ];
    }
}
