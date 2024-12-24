<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    //
     /**
     * Add an item to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartKey = 'cart:' . auth()->id();
        Redis::hset($cartKey, $request->product_id, $request->quantity);

        return response()->json(['message' => 'Item added to cart.'], 200);
    }

    /**
     * Remove an item from the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
        ]);

        $cartKey = 'cart:' . auth()->id();
        Redis::hdel($cartKey, $request->product_id);

        return response()->json(['message' => 'Item removed from cart.'], 200);
    }

    /**
     * View all items in the cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewCart()
    {
        $cartKey = 'cart:' . auth()->id();
        $cart = Redis::hgetall($cartKey);

        $formattedCart = collect($cart)->map(function ($quantity, $productId) {
            return [
                'product_id' => $productId,
                'quantity' => (int) $quantity,
            ];
        })->values();

        return response()->json($formattedCart, 200);
    }
}

