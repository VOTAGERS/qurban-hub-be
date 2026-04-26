<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Woo\OrderEventHandler;
use App\Services\Woo\CustomerEventHandler;
use App\Services\Woo\ProductEventHandler;

class ProcessWooWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $eventPayload
    ) {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        $event = $this->eventPayload['type'] ?? null;
        $data  = $this->eventPayload['data'] ?? [];

        match ($event) {
            'order.created',
            'order.updated' => app(OrderEventHandler::class)->handle($data),

            'customer.created',
            'customer.updated' => app(CustomerEventHandler::class)->handle($data),

            'product.created',
            'product.updated' => app(ProductEventHandler::class)->handle($data),

            default => null
        };
    }
}
