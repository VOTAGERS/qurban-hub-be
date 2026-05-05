<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
            'product.created',
            'product.updated' => app(ProductEventHandler::class)->handle($data),

            default => null
        };
    }
}
