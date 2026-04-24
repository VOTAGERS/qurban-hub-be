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

    public $event;
    public $payload;

    /**
     * Create a new job instance.
     */
    public function __construct($event, $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (!$this->event) {
            return;
        }

        switch ($this->event) {
            case 'order.created':
            case 'order.updated':
                app(OrderEventHandler::class)->handle($this->payload);
                break;

            case 'customer.created':
            case 'customer.updated':
                app(CustomerEventHandler::class)->handle($this->payload);
                break;

            case 'product.created':
            case 'product.updated':
                app(ProductEventHandler::class)->handle($this->payload);
                break;
        }
    }
}
