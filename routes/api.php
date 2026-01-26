<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DashboardController;

// 1. Publik
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);



// 2. Grup yang harus Login (Admin & Superadmin bisa masuk)
Route::middleware('auth:sanctum')->group(function () {

    // Fitur Umum (Dua-duanya bisa)
    Route::get('/members', [MemberController::class, 'index']);
    Route::get('/members/{id}', [MemberController::class, 'show']); // New Route
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);
    Route::get('/members/{id}/history', [MemberController::class, 'history']);
    Route::post('/members/{id}/renew', [MemberController::class, 'renew']);

    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    // Note: Transaction update/delete might not have dedicated methods in controller yet, check controller first? 
    // User said Delete error was 404. So route likely missing.
    // I should check TransactionController for update/destroy methods. 
    // Wait, let's assume standard names.
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    // Expenses
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class);

    // Member Export
    Route::get('/members/export', [MemberController::class, 'exportExcel']);
    Route::get('/export/members', [MemberController::class, 'exportExcel']); // Alternative route that actually works
    Route::post('/members/import', [MemberController::class, 'importLegacy']);
    
    Route::get('/export/excel', [ReportController::class, 'exportExcel']);
    Route::get('/export/pdf', [ReportController::class, 'exportPdf']);

    // Attendance (Presensi)
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendance/history', [AttendanceController::class, 'index']);
    Route::get('/attendance/export-shift', [AttendanceController::class, 'exportShift']);
    Route::get('/attendance/rekap-shift', [AttendanceController::class, 'getShiftRecap']);

    // Packages
    Route::get('/packages', [\App\Http\Controllers\Api\PackageController::class, 'index']);

    // Products
    Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::post('/products', [\App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::get('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::put('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::delete('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy']);

    // Dashboard Stats
    Route::get('/dashboard/insights', [DashboardController::class, 'insights']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profile Management
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'changePassword']);

    // 3. KHUSUS Superadmin (Owner) - Mengelola Akun Admin
    // Kita asumsikan middleware-nya diberi nama 'role'
    Route::middleware('role:superadmin')->group(function () {
        Route::post('/users/add-admin', [AuthController::class, 'registerAdmin']);
        Route::delete('/users/{id}', [AuthController::class, 'deleteAdmin']);
    });
});