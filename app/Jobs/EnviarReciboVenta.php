<?php

namespace App\Jobs;

use App\Services\EmailJSService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnviarReciboVenta implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $toEmail,
        public string $toName,
        public array $data,
    ) {}

    public function handle(EmailJSService $emailService): void
    {
        $emailService->sendReceipt($this->toEmail, $this->toName, $this->data);
    }
}
