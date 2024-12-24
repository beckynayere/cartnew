<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * Place an order from the user's cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrder()
    {
        $cartKey = 'cart:' . auth()->id();
        $cart = Redis::hgetall($cartKey);

        // Check if cart is empty
        if (empty($cart)) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        $total = 0;
        $orderItems = [];

        foreach ($cart as $productId => $quantity) {
            $product = Product::find($productId);

            // Handle product not found
            if (!$product) {
                return response()->json(['message' => "Product with ID $productId not found."], 404);
            }

            $price = $product->price;
            $total += $price * $quantity;

            $orderItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
            ];
        }

        // Create the order
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $total,
        ]);

        // Insert order items
        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        // Clear the cart
        Redis::del($cartKey);

        return response()->json(['message' => 'Order placed successfully.', 'order_id' => $order->id], 201);
    }
}
