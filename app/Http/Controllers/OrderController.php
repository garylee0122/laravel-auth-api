<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Jobs\ProcessOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        $items = $request->items;

        $order = DB::transaction(function () use ($user, $items) {
            $totalPrice = 0;
            $orderItemsData = [];

            // 取得傳入的商品 ID array
            $productIds = collect($items)->pluck('product_id')->unique()->values();
            // 將指定的商品鎖定，確保在處理訂單期間不會有其他請求修改這些商品的庫存
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                // 判斷商品是否存在
                if (!$product) {
                    Log::warning("Product {$item['product_id']} does not exist.");
                    throw ValidationException::withMessages([
                        'items' => ["Product {$item['product_id']} does not exist."],
                    ]);
                }

                // 判斷庫存是否足夠
                if ($product->stock < $item['quantity']) {
                    Log::warning("Product {$product->id} stock is insufficient. Requested: {$item['quantity']}, Available: {$product->stock}");
                    throw ValidationException::withMessages([
                        'items' => ["Product {$product->id} stock is insufficient."],
                    ]);
                }

                $product->decrement('stock', $item['quantity']);
                //$product->stock -= $item['quantity'];
                //$product->save();

                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                ];
            }

            $order = $user->orders()->create([
                'total_price' => $totalPrice,
                'status' => Order::STATUS_PENDING,
            ]);

            foreach ($orderItemsData as $data) {
                $order->items()->create($data);
            }

            return $order->load('items.product');
        });

        // 清掉產品相關的快取，確保後續請求能看到最新的庫存狀態
        Cache::tags(['products'])->flush();
        
        /* Region : 將訂單處理的工作推送到 Queue 中，讓它在背景中執行，不會阻塞用戶的請求 START */
        
        // 這裡是為了交給 Laravel 的 Queue 系統來處理訂單的後續流程
        // ProcessOrder::dispatch($order->id);
        
        // 這裡是為了給 ASP.NET 的 Worker Service 處理
        // 所以使用 Redis 的 rpush 方法將訂單資訊以 JSON 格式推送到指定的隊列中
        Redis::connection('queue_raw')->rpush('order_queue', json_encode([
            'OrderId' => $order->id,
            'RetryCount' => 0,
        ], JSON_UNESCAPED_UNICODE));

        /* Region : 將訂單處理的工作推送到 Queue 中，讓它在背景中執行，不會阻塞用戶的請求 END */

        return ApiResponse::success(
            new OrderResource($order),
            'Order created successfully, processing in background',
            201
        );
    }

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(5);

        return ApiResponse::success(
            OrderResource::collection($orders),
            'get all orders by ' . $request->user()->name . ' successfully'
        );
    }

    public function show(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with('items.product')
            ->findOrFail($id);

        return ApiResponse::success(
            new OrderResource($order),
            'get order (' . $order->id . ') by ' . $request->user()->name . ' successfully'
        );
    }
}
