<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\QuestController;
use App\Http\Controllers\Admin\SchoolDataController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentProgressController;
use App\Http\Controllers\Admin\TierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Student\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WaliKelasController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false,
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/school', [SchoolDataController::class, 'edit'])->name('school');
    Route::post('/school', [SchoolDataController::class, 'update'])->name('school.update');
    Route::delete('/school/image/{type}', [SchoolDataController::class, 'deleteImage'])->name('school.image.delete');

    Route::get('/classes', [ClassRoomController::class, 'index'])->name('classes');
    Route::post('/classes', [ClassRoomController::class, 'store'])->name('classes.store');
    Route::patch('/classes/{class}', [ClassRoomController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{class}', [ClassRoomController::class, 'destroy'])->name('classes.destroy');

    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::patch('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
    Route::get('/students/{student}/qrcode', [StudentController::class, 'qrCode'])->name('students.qrcode');
    Route::get('/students/{student}/qrcode/download', [StudentController::class, 'qrCode'])->name('students.qrcode.download');

    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/tiers', [TierController::class, 'index'])->name('tiers');
    Route::post('/tiers', [TierController::class, 'store'])->name('tiers.store');
    Route::patch('/tiers/{tier}', [TierController::class, 'update'])->name('tiers.update');
    Route::delete('/tiers/{tier}', [TierController::class, 'destroy'])->name('tiers.destroy');

    Route::get('/quests', [QuestController::class, 'index'])->name('quests');
    Route::post('/quests', [QuestController::class, 'store'])->name('quests.store');
    Route::patch('/quests/{quest}', [QuestController::class, 'update'])->name('quests.update');
    Route::delete('/quests/{quest}', [QuestController::class, 'destroy'])->name('quests.destroy');

    Route::get('/gamification', [TierController::class, 'index'])->name('gamification');
    Route::get('/student-progress', [StudentProgressController::class, 'index'])->name('student-progress');
    Route::get('/audit', [AuditController::class, 'index'])->name('audit');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
    Route::get('/settings/backup/{filename}/download', [SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::get('/settings/backups/list', [SettingsController::class, 'listBackups'])->name('settings.backups.list');
    Route::post('/settings/restore', [SettingsController::class, 'restore'])->name('settings.restore');
    Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');
});

Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::patch('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/transactions/student/{student}', [TransactionController::class, 'getStudent'])->name('transactions.student');
    Route::post('/transactions/import', [TransactionController::class, 'import'])->name('transactions.import');
    Route::get('/transactions/template', [TransactionController::class, 'downloadTemplate'])->name('transactions.template');
});

Route::middleware(['auth', 'role:admin,staff,wali_kelas'])->group(function () {
    Route::get('/history', [HistoryController::class, '__invoke'])->name('history');
    Route::get('/history/excel', [HistoryController::class, 'exportExcel'])->name('history.excel');
    Route::get('/history/pdf', [HistoryController::class, 'exportPdf'])->name('history.pdf');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/reports/buku-tabungan/{student}', [ReportController::class, 'bukuTabungan'])->name('reports.buku-tabungan');

    Route::get('/wali-kelas/students', [WaliKelasController::class, 'students'])->name('wali-kelas.students');
});

Route::prefix('student')->name('student.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate')->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:student')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    });
});

require __DIR__.'/auth.php';
