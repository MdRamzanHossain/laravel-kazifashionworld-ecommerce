<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendOrderSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job (exponential backoff).
     */
    public array $backoff = [10, 60];

    public function __construct(public SmsLog $smsLog) {}

    public function handle(): void
    {
        $this->smsLog->increment('attempts');

        try {
            SmsService::send($this->smsLog);
        } catch (Throwable $e) {
            $this->smsLog->update([
                'error_message' => "Attempt {$this->smsLog->attempts} failed: " . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure after all retry attempts are exhausted.
     */
    public function failed(?Throwable $exception): void
    {
        $this->smsLog->update([
            'status'        => 'failed',
            'error_message' => "Permanently failed after {$this->tries} attempts: " . $exception?->getMessage(),
        ]);
    }
}