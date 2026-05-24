<?php

use App\Http\Controllers\Api\StudentAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/student/login', [StudentAuthController::class, 'login'])->name('api.student.login')->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/me', [StudentAuthController::class, 'me'])->name('api.student.me');
    Route::get('/student/transactions', [StudentAuthController::class, 'transactions'])->name('api.student.transactions');
    Route::get('/student/dashboard', [StudentAuthController::class, 'dashboard'])->name('api.student.dashboard');
});
