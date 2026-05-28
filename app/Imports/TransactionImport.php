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

    private int $userId;

    public function __construct()
    {
        $this->errors = collect();
        $this->userId = auth()->id() ?? 1;
    }

    public function collection(Collection $rows)
    {
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
                'created_by' => $this->userId,
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

        DB::transaction(function () use ($transactions, $studentIds) {
            Student::whereIn('id', $studentIds)->lockForUpdate()->get();

            foreach (array_chunk($transactions, 100) as $chunk) {
                Transaction::insert($chunk);
            }

            $this->recalculateBalances();
        });
    }

    private function recalculateBalances(): void
    {
        $allStudentIds = Student::whereHas('transactions')->pluck('id');

        foreach ($allStudentIds as $id) {
            $runningBalance = 0;
            $updates = [];

            $transactions = Transaction::where('student_id', $id)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get(['id', 'type', 'amount', 'balance_after']);

            foreach ($transactions as $t) {
                $runningBalance += $t->type === 'setor' ? $t->amount : -$t->amount;
                if ($t->balance_after !== $runningBalance) {
                    $updates[] = [
                        'id' => $t->id,
                        'balance_after' => $runningBalance,
                    ];
                }
            }

            Student::where('id', $id)->update(['balance' => $runningBalance]);

            foreach (array_chunk($updates, 100) as $chunk) {
                $cases = [];
                $ids = [];
                foreach ($chunk as $u) {
                    $cases[] = "WHEN {$u['id']} THEN {$u['balance_after']}";
                    $ids[] = $u['id'];
                }
                if (! empty($cases)) {
                    DB::statement(
                        'UPDATE transactions SET balance_after = CASE id '
                        .implode(' ', $cases)
                        .' END WHERE id IN ('.implode(',', $ids).')'
                    );
                }
            }
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
