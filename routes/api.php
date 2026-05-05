<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\OccupantController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseOccupantController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DuesTypeController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh-token', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    
    // Khusus RT
    Route::get('/infort', [InfoController::class, 'infort'])->middleware('is_rt');
    
    // Khusus Warga
    Route::middleware('is_warga')->group(function () {
        Route::get('/infowrg', [InfoController::class, 'infowrg']);
        Route::get('/warga/my-houses', [WargaController::class, 'myHouses']);
        Route::get('/warga/my-payments', [WargaController::class, 'myPayments']);
        Route::post('/warga/pay', [WargaController::class, 'payDues']);
    });
    
    // Bersama (RT & Warga)
    Route::get('/infobersama', [InfoController::class, 'infobersama']);
    
    // CRUD Occupants (RT Only)
    Route::middleware('is_rt')->group(function () {
        Route::apiResource('occupants', OccupantController::class);
        Route::apiResource('houses', HouseController::class);
        Route::get('/houses/{house}/occupants', [HouseController::class, 'occupants']);
        Route::apiResource('house-occupants', HouseOccupantController::class);
        Route::apiResource('payments', PaymentController::class);
        Route::apiResource('expenses', ExpenseController::class);
        Route::apiResource('dues-types', DuesTypeController::class);
        
        // Dashboard Reports
        Route::get('/dashboard/report-monthly', [DashboardController::class, 'getMonthlyReport']);

        // Get Proof Photo
        Route::get('/ktp-photo/{filename}', [OccupantController::class, 'showKtpPhoto']);
        Route::get('/payment-proof/{filename}', [PaymentController::class, 'getProof']);

    });
    
});
