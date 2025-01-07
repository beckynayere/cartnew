Route::middleware('auth:sanctum')->group(function () {
    Route::post('cart/add', [CartController::class, 'addToCart']);
    Route::post('cart/remove', [CartController::class, 'removeFromCart']);
    Route::get('cart/view', [CartController::class, 'viewCart']);
    Route::post('order/place', [OrderController::class, 'placeOrder']);
    Route::get('orders', [OrderController::class, 'viewOrders']);
});
