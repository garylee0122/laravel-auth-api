<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreOrderRequest;
use App\Helpers\ApiResponse;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        $items = $request->items;

        // 🔥 用 transaction（超重要）
        $orderItems = DB::transaction(function () use ($user, $items) {

            $totalPrice = 0;
            $orderItemsData = [];

            foreach ($items as $item) {

                $product = Product::findOrFail($item['product_id']);

                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'price' => $product->price, // 🔥 記錄當下價格
                    'quantity' => $item['quantity']
                ];
            }

            // 建立 Order
            $order = $user->orders()->create([
                'total_price' => $totalPrice
            ]);

            // 建立 OrderItems
            foreach ($orderItemsData as $data) {
                $order->items()->create($data);
            }

            return response()->json($order->load('items.product'));
        });

        return ApiResponse::success($orderItems, 'Order created successfully', 201);
    }
}
