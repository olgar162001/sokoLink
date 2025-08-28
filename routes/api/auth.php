<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    // Seller / Customer Registration
    Route::post('/register', [AuthController::class, 'register']); 

    // Login (handles both email+password for admin and phone+OTP for sellers)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);


    // Logout (requires authentication)
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

// Users management (Admin only)
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    // List all users (filter by role/status inside controller)
    Route::get('/users', [UserController::class, 'index']);

    // Update user status/role
    Route::patch('/users/{id}', [UserController::class, 'update']);
});
