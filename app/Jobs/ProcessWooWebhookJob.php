<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Woo\OrderEventHandler;
use App\Services\Woo\CustomerEventHandler;
use App\Services\Woo\ProductEventHandler;

class ProcessWooWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $event,
        public array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        match ($this->event) {
            'order.created',
            'order.updated' => app(OrderEventHandler::class)->handle($this->payload),

            'customer.created',
            'customer.updated' => app(CustomerEventHandler::class)->handle($this->payload),

            'product.created',
            'product.updated' => app(ProductEventHandler::class)->handle($this->payload),

            default => null
        };
    }
}
