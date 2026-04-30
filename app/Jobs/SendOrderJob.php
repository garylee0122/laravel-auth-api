<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderJob implements ShouldQueue
{
    use Queueable;

    protected int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(): void
    {
        Log::info("Processing order: " . $this->orderId);

        // 模擬耗時工作
        sleep(5);

        Log::info("Order done: " . $this->orderId);
    }
}
