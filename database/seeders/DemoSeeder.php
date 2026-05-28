<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@smkglobin.sch.id')->first();
        $walasX = User::where('email', 'walas@smkglobin.sch.id')->first();
        $walasXI = User::where('email', 'walas.xi@smkglobin.sch.id')->first();

        $kelas = [
            'X-A' => $walasX->id,
            'X-B' => $walasX->id,
            'XI-A' => $walasXI->id,
            'XI-B' => $walasXI->id,
            'XII-A' => null,
            'XII-B' => null,
        ];

        $classModels = [];
        foreach ($kelas as $name => $walasId) {
            $classModels[$name] = ClassRoom::create(['name' => $name, 'wali_kelas_id' => $walasId]);
        }

        $students = [
            ['nis' => '2024001', 'name' => 'Ahmad Rizki', 'class' => 'X-A'],
            ['nis' => '2024002', 'name' => 'Siti Nurhaliza', 'class' => 'X-A'],
            ['nis' => '2024003', 'name' => 'Bambang Suprayogi', 'class' => 'X-A'],
            ['nis' => '2024004', 'name' => 'Dewi Sartika', 'class' => 'X-A'],
            ['nis' => '2024005', 'name' => 'Eko Prasetyo', 'class' => 'X-A'],
            ['nis' => '2024006', 'name' => 'Fitri Handayani', 'class' => 'X-B'],
            ['nis' => '2024007', 'name' => 'Gilang Permana', 'class' => 'X-B'],
            ['nis' => '2024008', 'name' => 'Hesti Purnamasari', 'class' => 'X-B'],
            ['nis' => '2024009', 'name' => 'Irfan Maulana', 'class' => 'X-B'],
            ['nis' => '2024010', 'name' => 'Joko Susilo', 'class' => 'XI-A'],
            ['nis' => '2024011', 'name' => 'Kartika Sari', 'class' => 'XI-A'],
            ['nis' => '2024012', 'name' => 'Lukman Hakim', 'class' => 'XI-A'],
            ['nis' => '2024013', 'name' => 'Maya Anggraini', 'class' => 'XI-B'],
            ['nis' => '2024014', 'name' => 'Nanda Pratama', 'class' => 'XI-B'],
            ['nis' => '2024015', 'name' => 'Oki Setiawan', 'class' => 'XII-A'],
            ['nis' => '2024016', 'name' => 'Putri Ayu', 'class' => 'XII-A'],
            ['nis' => '2024017', 'name' => 'Qori Amalia', 'class' => 'XII-B'],
            ['nis' => '2024018', 'name' => 'Rudi Hartono', 'class' => 'XII-B'],
        ];

        $studentModels = [];
        foreach ($students as $s) {
            $student = Student::create([
                'nis' => $s['nis'],
                'name' => $s['name'],
                'phone' => '08'.fake()->numerify('##########'),
                'class_id' => $classModels[$s['class']]->id,
                'qr_token' => 'demo-qr-'.$s['nis'],
            ]);
            $student->password = 'smkglobin';
            $student->save();
            $studentModels[$s['nis']] = $student;
        }

        $transactions = [
            ['nis' => '2024001', 'type' => 'setor', 'amount' => 50000, 'date' => '-5 days'],
            ['nis' => '2024001', 'type' => 'setor', 'amount' => 25000, 'date' => '-3 days'],
            ['nis' => '2024001', 'type' => 'setor', 'amount' => 30000, 'date' => '-1 days'],
            ['nis' => '2024002', 'type' => 'setor', 'amount' => 100000, 'date' => '-10 days'],
            ['nis' => '2024002', 'type' => 'tarik', 'amount' => 20000, 'date' => '-7 days'],
            ['nis' => '2024002', 'type' => 'setor', 'amount' => 50000, 'date' => '-2 days'],
            ['nis' => '2024003', 'type' => 'setor', 'amount' => 25000, 'date' => '-8 days'],
            ['nis' => '2024004', 'type' => 'setor', 'amount' => 150000, 'date' => '-15 days'],
            ['nis' => '2024004', 'type' => 'setor', 'amount' => 50000, 'date' => '-1 days'],
            ['nis' => '2024005', 'type' => 'setor', 'amount' => 10000, 'date' => '-12 days'],
            ['nis' => '2024006', 'type' => 'setor', 'amount' => 75000, 'date' => '-6 days'],
            ['nis' => '2024006', 'type' => 'tarik', 'amount' => 25000, 'date' => '-4 days'],
            ['nis' => '2024010', 'type' => 'setor', 'amount' => 200000, 'date' => '-20 days'],
            ['nis' => '2024010', 'type' => 'setor', 'amount' => 100000, 'date' => '-5 days'],
            ['nis' => '2024011', 'type' => 'setor', 'amount' => 50000, 'date' => '-14 days'],
            ['nis' => '2024011', 'type' => 'setor', 'amount' => 50000, 'date' => '-7 days'],
            ['nis' => '2024015', 'type' => 'setor', 'amount' => 300000, 'date' => '-30 days'],
            ['nis' => '2024015', 'type' => 'tarik', 'amount' => 50000, 'date' => '-10 days'],
            ['nis' => '2024016', 'type' => 'setor', 'amount' => 25000, 'date' => '-3 days'],
        ];

        DB::transaction(function () use ($transactions, $studentModels, $admin) {
            foreach ($transactions as $t) {
                $student = $studentModels[$t['nis']];
                Student::lockForUpdate()->find($student->id);

                $prevBalance = $student->balance;
                $balanceAfter = $t['type'] === 'setor'
                    ? $prevBalance + $t['amount']
                    : $prevBalance - $t['amount'];

                Transaction::create([
                    'student_id' => $student->id,
                    'type' => $t['type'],
                    'amount' => $t['amount'],
                    'balance_after' => $balanceAfter,
                    'transaction_date' => Carbon::parse($t['date'])->format('Y-m-d'),
                    'created_by' => $admin->id,
                    'note' => $t['type'] === 'setor' ? 'Setoran tabungan' : 'Penarikan tabungan',
                ]);

                $student->balance = $balanceAfter;
                $student->save();
            }
        });
    }
}
