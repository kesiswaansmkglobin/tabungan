<?php

namespace App\Imports;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentImport implements SkipsEmptyRows, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    private ?Collection $classCache = null;

    private array $existingNisCache = [];

    public function collection(Collection $rows)
    {
        if ($this->classCache === null) {
            $this->classCache = ClassRoom::pluck('id', 'name');
        }

        foreach ($rows as $row) {
            $kelas = $row['kelas'] ?? $row['Kelas'] ?? null;
            $nis = $row['nis'] ?? $row['NIS'] ?? $row['n_i_s'] ?? null;
            $nama = $row['nama'] ?? $row['Nama'] ?? null;
            $phone = $row['phone'] ?? $row['Phone'] ?? $row['no_hp'] ?? null;

            if (! $nis || ! $nama || ! $kelas) {
                continue;
            }

            $classId = $this->classCache->get($kelas);
            if (! $classId) {
                continue;
            }

            if (! array_key_exists($nis, $this->existingNisCache)) {
                $this->existingNisCache[$nis] = Student::where('nis', $nis)->exists();
            }
            if ($this->existingNisCache[$nis]) {
                continue;
            }

            $student = Student::create([
                'nis' => $nis,
                'name' => $nama,
                'phone' => $phone,
                'class_id' => $classId,
            ]);
            $student->password = Hash::make('smkglobin');
            $student->save();
        }
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:20',
            'nama' => 'required|string|max:255',
            'kelas' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20|regex:/^(\+62|62|0)8[0-9]{7,12}$/',
            'no_hp' => 'nullable|string|max:20|regex:/^(\+62|62|0)8[0-9]{7,12}$/',
        ];
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
