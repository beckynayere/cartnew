<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Add an item to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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

        if (empty($cart)) {
            return response()->json(['message' => 'Cart is empty.'], 200);
        }

        $formattedCart = collect($cart)->map(function ($quantity, $productId) {
            $product = Product::find($productId);

            return [
                'product_id' => $productId,
                'quantity' => (int) $quantity,
                'product_name' => $product ? $product->name : 'Unknown Product',
                'price' => $product ? $product->price : 0,
            ];
        })->values();

        return response()->json($formattedCart, 200);
    }

    /**
     * Place an order with the items in the cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrder()
    {
        $cartKey = 'cart:' . auth()->id();
        $cart = Redis::hgetall($cartKey);

        if (empty($cart)) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => collect($cart)->reduce(function ($carry, $quantity, $productId) {
                $product = Product::find($productId);
                return $carry + ($product ? $product->price * $quantity : 0);
            }, 0),
        ]);

        foreach ($cart as $productId => $quantity) {
            $product = Product::find($productId);

            if ($product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }
        }

        Redis::del($cartKey);

        return response()->json(['message' => 'Order placed successfully.', 'order_id' => $order->id], 201);
    }
}
