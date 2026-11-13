<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BookController;

// Route Public (Tanpa Token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route yang Membutuhkan Token (Protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);
    
    // Books CRUD
    Route::apiResource('books', BookController::class);

    // Borrowings & Returns
    Route::get('/borrowings', [\App\Http\Controllers\Api\BorrowingController::class, 'index']);
    Route::post('/borrowings', [\App\Http\Controllers\Api\BorrowingController::class, 'store']);
    Route::post('/borrowings/{borrowing}/return', [\App\Http\Controllers\Api\BorrowingController::class, 'returnBook']);
});

