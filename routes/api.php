Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);
});