use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BorrowingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);
    
    // Books CRUD
    Route::apiResource('books', BookController::class);

    // Borrowings & Returns
    Route::get('/borrowings', [BorrowingController::class, 'index']);
    Route::post('/borrowings', [BorrowingController::class, 'store']);
    Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook']);
});