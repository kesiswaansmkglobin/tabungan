<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings');
    }

    public function backup(Request $request): BinaryFileResponse|JsonResponse|RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, auth()->user()->password)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Password salah.'], 422);
            }

            return back()->with('error', 'Password salah.');
        }

        $filename = 'backup-'.date('Y-m-d-His').'.sql';
        $backupDir = storage_path('app/backups');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $path = $backupDir.'/'.$filename;

        try {
            $pdo = DB::connection()->getPdo();
            $dbName = config('database.connections.mysql.database');

            $sql = "-- Tabungan Siswa SMK Globin\n-- Database: {$dbName}\n-- Generated: ".date('Y-m-d H:i:s')."\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $sql .= "-- Table: {$table}\n\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
                $sql .= $create[1].";\n\n";

                $stmt = $pdo->query("SELECT * FROM `{$table}`");
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (! empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $colNames = '`'.implode('`, `', $columns).'`';

                    $chunks = array_chunk($rows, 200);
                    foreach ($chunks as $chunk) {
                        $values = [];
                        foreach ($chunk as $row) {
                            $escaped = [];
                            foreach ($columns as $col) {
                                $val = $row[$col];
                                if ($val === null) {
                                    $escaped[] = 'NULL';
                                } else {
                                    $escaped[] = $pdo->quote($val);
                                }
                            }
                            $values[] = '('.implode(', ', $escaped).')';
                        }
                        $sql .= "INSERT INTO `{$table}` ({$colNames}) VALUES\n".implode(",\n", $values).";\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

            file_put_contents($path, $sql);

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['filename' => $filename, 'size' => strlen($sql)])
                ->log('backup');

            return response()->download($path, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Gagal backup database: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal backup database: '.$e->getMessage());
        }
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $path = storage_path('app/backups/'.basename($filename));

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function listBackups(): JsonResponse
    {
        $files = collect(Storage::files('backups'))
            ->filter(fn ($f) => str_ends_with($f, '.sql'))
            ->map(fn ($f) => [
                'name' => basename($f),
                'size' => Storage::size($f),
                'date' => Storage::lastModified($f),
                'url' => route('admin.settings.backup.download', basename($f)),
            ])
            ->sortByDesc('date')
            ->values();

        return response()->json($files);
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
            'file' => 'required|file|mimes:sql,txt',
        ]);

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Password salah.');
        }

        $sqlContent = file_get_contents($request->file('file')->getRealPath());

        if ($sqlContent === false || trim($sqlContent) === '') {
            return back()->with('error', 'File backup kosong atau tidak terbaca.');
        }

        $dangerous = ['DROP DATABASE', 'DROP USER', 'GRANT', 'REVOKE', 'CREATE USER',
            'ALTER USER', 'CREATE DATABASE', 'ALTER DATABASE',
            'SELECT INTO OUTFILE', 'INTO DUMPFILE', 'LOAD DATA',
            'CREATE FUNCTION', 'CREATE PROCEDURE', 'CREATE EVENT', 'CREATE TRIGGER',
            'SLAVE', 'MASTER', 'SUPER', 'FILE', 'PROCESS',
        ];

        $upper = strtoupper(preg_replace('/\s+/', ' ', $sqlContent));
        foreach ($dangerous as $keyword) {
            if (str_contains($upper, $keyword)) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties(['file' => $request->file('file')->getClientOriginalName()])
                    ->log('restore_ditolak');

                return back()->with('error', 'File backup mengandung perintah SQL yang tidak diizinkan.');
            }
        }

        $sqlContent = preg_replace(
            '/^CREATE TABLE\s+(`[^`]+`)/im',
            "DROP TABLE IF EXISTS $1;\nCREATE TABLE $1",
            $sqlContent
        );

        if (DB::connection()->getDriverName() !== 'mysql') {
            return back()->with('error', 'Restore hanya mendukung database MySQL.');
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            $db = config('database.connections.mysql');
            $mysqli = new \mysqli($db['host'], $db['username'], $db['password'] ?? '', $db['database'], $db['port']);

            if ($mysqli->connect_error) {
                throw new \Exception('Koneksi database gagal: '.$mysqli->connect_error);
            }

            if (! $mysqli->multi_query($sqlContent)) {
                throw new \Exception($mysqli->error);
            }

            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            if ($mysqli->error) {
                throw new \Exception($mysqli->error);
            }

            $mysqli->close();

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['file' => $request->file('file')->getClientOriginalName()])
                ->log('restore');

            return back()->with('success', 'Database berhasil direstore.');
        } catch (\Exception $e) {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['error' => $e->getMessage()])
                ->log('restore_gagal');

            return back()->with('error', 'Gagal restore database: '.$e->getMessage());
        }
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:HAPUS',
        ]);

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Password salah.');
        }

        try {
            DB::beginTransaction();

            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            $tables = [
                'offline_sync_keys',
                'student_quest_completions',
                'student_progress',
                'student_qr_tokens',
                'transactions',
                'students',
                'classes',
            ];

            foreach ($tables as $table) {
                DB::delete("DELETE FROM `{$table}`");
                if (DB::connection()->getDriverName() === 'mysql') {
                    DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
                }
            }

            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            DB::commit();

            activity()
                ->causedBy(auth()->user())
                ->log('reset_data');

            return back()->with('success', 'Semua data siswa, kelas, dan transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            DB::rollBack();

            return back()->with('error', 'Gagal mereset database: '.$e->getMessage());
        }
    }
}
