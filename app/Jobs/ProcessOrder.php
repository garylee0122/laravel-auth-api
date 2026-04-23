<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::with('items.product')->find($this->orderId);

        if (!$order) {
            Log::warning("ProcessOrder skipped: order {$this->orderId} not found");
            return;
        }

        sleep(3);

        Log::info("Order processed: {$order->id}");
    }
}
