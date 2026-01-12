<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ReportController;

// 1. Publik
Route::post('/login', [AuthController::class, 'login']);

// 2. Grup yang harus Login (Admin & Superadmin bisa masuk)
Route::middleware('auth:sanctum')->group(function () {
    
    // Fitur Umum (Dua-duanya bisa)
    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/export/excel', [ReportController::class, 'exportExcel']);
    Route::get('/export/pdf', [ReportController::class, 'exportPdf']);

    // 3. KHUSUS Superadmin (Owner) - Mengelola Akun Admin
    // Kita asumsikan middleware-nya diberi nama 'role'
    Route::middleware('role:superadmin')->group(function () {
        Route::post('/users/add-admin', [AuthController::class, 'registerAdmin']);
        Route::delete('/users/{id}', [AuthController::class, 'deleteAdmin']);
    });
});