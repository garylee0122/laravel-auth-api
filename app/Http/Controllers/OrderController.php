<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreOrderRequest;
use App\Helpers\ApiResponse;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        $items = $request->items;

        // 🔥 用 transaction（超重要）
        $orderRec = DB::transaction(function () use ($user, $items) {

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
                'total_price' => $totalPrice,
                'status' => Order::STATUS_PENDING
            ]);

            // 建立 OrderItems
            foreach ($orderItemsData as $data) {
                $order->items()->create($data);
            }

            return $order->load('items.product');
        });

        return ApiResponse::success(new OrderResource($orderRec), 'Order created successfully', 201);
    }

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product') // 🔥 關聯載入
            ->latest() // 按照 created_at 降序排列，讓最新的訂單在前面
            ->paginate(5); // 分頁，每頁5筆資料
            //->get(); //取得全部訂單 //paginate() 和 get() 只能二選一，不能同時使用

        return ApiResponse::success(OrderResource::collection($orders), 'get all orders by ' . $request->user()->name . ' successfully');
    }

    public function show(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with('items.product')
            ->findOrFail($id); // 🔥 防止偷看

        return ApiResponse::success(new OrderResource($order), 'get order (' . $order->id . ') by ' . $request->user()->name . ' successfully');
    }
}
