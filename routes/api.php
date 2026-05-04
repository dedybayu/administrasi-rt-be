<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\OccupantController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh-token', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    
    // Khusus RT
    Route::get('/infort', [InfoController::class, 'infort'])->middleware('is_rt');
    
    // Khusus Warga
    Route::get('/infowrg', [InfoController::class, 'infowrg'])->middleware('is_warga');
    
    // Bersama (RT & Warga)
    Route::get('/infobersama', [InfoController::class, 'infobersama']);
    
    // CRUD Occupants (RT Only)
    Route::middleware('is_rt')->group(function () {
        Route::apiResource('occupants', OccupantController::class);
    });
    
});
