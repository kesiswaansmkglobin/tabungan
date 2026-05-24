<?php

namespace App\Imports;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentImport implements SkipsEmptyRows, ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    private ?Collection $classCache = null;

    private array $existingNisCache = [];

    public function model(array $row)
    {
        if ($this->classCache === null) {
            $this->classCache = ClassRoom::pluck('id', 'name');
        }

        $kelas = $row['kelas'] ?? $row['Kelas'] ?? null;
        $nis = $row['nis'] ?? $row['NIS'] ?? $row['n_i_s'] ?? null;
        $nama = $row['nama'] ?? $row['Nama'] ?? null;
        $phone = $row['phone'] ?? $row['Phone'] ?? $row['no_hp'] ?? null;

        if (! $nis || ! $nama || ! $kelas) {
            return null;
        }

        $classId = $this->classCache->get($kelas);
        if (! $classId) {
            return null;
        }

        if (! array_key_exists($nis, $this->existingNisCache)) {
            $this->existingNisCache[$nis] = Student::where('nis', $nis)->exists();
        }
        if ($this->existingNisCache[$nis]) {
            return null;
        }

        return new Student([
            'nis' => $nis,
            'name' => $nama,
            'phone' => $phone,
            'class_id' => $classId,
            'password' => Hash::make('smkglobin'),
        ]);
    }

    public function rules(): array
    {
        return [];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
