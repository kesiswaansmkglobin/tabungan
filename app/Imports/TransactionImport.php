<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TransactionImport implements SkipsEmptyRows, ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    private array $nisCache = [];

    private array $nameCache = [];

    private int $imported = 0;

    private Collection $errors;

    public function __construct()
    {
        $this->errors = collect();
    }

    public function collection(Collection $rows)
    {
        $this->nisCache = Student::pluck('id', 'nis')->toArray();
        $this->nameCache = Student::pluck('id', 'name')->toArray();

        $transactions = [];
        $balanceAdjustments = [];
        $runningBalances = [];

        foreach ($rows as $row) {
            $tanggal = $row['tanggal'] ?? $row['Tanggal'] ?? null;
            $nis = $row['nis'] ?? $row['NIS'] ?? $row['n_i_s'] ?? null;
            $nama = $row['nama'] ?? $row['Nama'] ?? null;
            $jenis = $row['jenis'] ?? $row['Jenis'] ?? null;
            $jumlah = $row['jumlah'] ?? $row['Jumlah'] ?? $row['nominal'] ?? null;

            if (! $tanggal || ! $jenis || ! $jumlah) {
                continue;
            }

            if (! $nis && ! $nama) {
                continue;
            }

            try {
                $tanggal = $this->parseDate($tanggal);
            } catch (\Exception) {
                $this->errors->push("Format tanggal tidak valid ({$tanggal})");

                continue;
            }

            $jenis = strtolower(trim($jenis));
            if (! in_array($jenis, ['setor', 'tarik'])) {
                $this->errors->push("Jenis harus 'setor' atau 'tarik', bukan '{$jenis}'");

                continue;
            }

            $studentId = null;
            $label = '';

            if ($nis) {
                $studentId = $this->nisCache[$nis] ?? null;
                $label = "NIS {$nis}";
            }

            if (! $studentId && $nama) {
                $studentId = $this->nameCache[$nama] ?? null;
                $label = "Nama '{$nama}'";
            }

            if (! $studentId) {
                $this->errors->push("Siswa {$label} tidak ditemukan di database");

                continue;
            }

            $amount = (int) preg_replace('/[^0-9]/', '', (string) $jumlah);
            if ($amount < 1) {
                $this->errors->push("{$label}: jumlah harus lebih dari 0");

                continue;
            }

            $note = $row['keterangan'] ?? $row['Keterangan'] ?? $row['note'] ?? null;
            if (! $note && $jenis === 'setor') {
                $note = 'Setoran harian siswa';
            }

            if (! isset($runningBalances[$studentId])) {
                $runningBalances[$studentId] = Student::where('id', $studentId)->value('balance') ?? 0;
            }
            $runningBalances[$studentId] += $jenis === 'setor' ? $amount : -$amount;

            $transactions[] = [
                'student_id' => $studentId,
                'type' => $jenis,
                'amount' => $amount,
                'balance_after' => $runningBalances[$studentId],
                'transaction_date' => $tanggal,
                'note' => $note,
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (! isset($balanceAdjustments[$studentId])) {
                $balanceAdjustments[$studentId] = 0;
            }
            $balanceAdjustments[$studentId] += $jenis === 'setor' ? $amount : -$amount;

            $this->imported++;
        }

        if (empty($transactions)) {
            return;
        }

        $studentIds = array_keys($balanceAdjustments);

        DB::transaction(function () use ($transactions, $balanceAdjustments, $studentIds) {
            Student::whereIn('id', $studentIds)->lockForUpdate()->get();

            foreach (array_chunk($transactions, 100) as $chunk) {
                Transaction::insert($chunk);
            }

            foreach ($balanceAdjustments as $studentId => $adjustment) {
                Student::where('id', $studentId)->increment('balance', $adjustment);
            }

            $this->recalculateBalances();
        });
    }

    private function recalculateBalances(): void
    {
        $studentIds = Student::whereHas('transactions')->pluck('id');

        foreach ($studentIds as $id) {
            $balance = Transaction::where('student_id', $id)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get()
                ->reduce(function ($carry, $t) {
                    return $carry + ($t->type === 'setor' ? $t->amount : -$t->amount);
                }, 0);

            Student::where('id', $id)->update(['balance' => $balance]);
            Transaction::where('student_id', $id)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->each(function ($t, $index) use ($id) {
                    $prevBalance = Transaction::where('student_id', $id)
                        ->where(function ($q) use ($t) {
                            $q->where('transaction_date', '<', $t->transaction_date)
                                ->orWhere(function ($q) use ($t) {
                                    $q->where('transaction_date', $t->transaction_date)
                                        ->where('id', '<', $t->id);
                                });
                        })
                        ->sum(DB::raw("CASE WHEN type = 'setor' THEN amount ELSE -amount END"));

                    $balanceAfter = $prevBalance + ($t->type === 'setor' ? $t->amount : -$t->amount);
                    if ($t->balance_after !== $balanceAfter) {
                        Transaction::where('id', $t->id)->update(['balance_after' => $balanceAfter]);
                    }
                });
        }
    }

    private function parseDate(string $value): string
    {
        $value = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            $parts = explode('/', $value);

            return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            $parts = explode('-', $value);

            return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
        }

        if (preg_match('/^\d{1,2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4}$/i', $value)) {
            $map = [
                'januari' => 'January', 'februari' => 'February', 'maret' => 'March',
                'april' => 'April', 'mei' => 'May', 'juni' => 'June',
                'juli' => 'July', 'agustus' => 'August', 'september' => 'September',
                'oktober' => 'October', 'november' => 'November', 'desember' => 'December',
            ];
            $value = str_ireplace(array_keys($map), array_values($map), $value);
            $ts = strtotime($value);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        if (preg_match('/^\d{1,2}\s+(Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)\s+\d{4}$/i', $value)) {
            $map = [
                'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
                'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'agu' => 'Aug',
                'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov', 'des' => 'Dec',
            ];
            $value = str_ireplace(array_keys($map), array_values($map), $value);
            $ts = strtotime($value);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        if (is_numeric($value)) {
            $unix = ($value - 25569) * 86400;

            return date('Y-m-d', $unix);
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Format tanggal tidak dikenal: {$value}");
        }

        return date('Y-m-d', $timestamp);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getErrors(): Collection
    {
        return $this->errors;
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
