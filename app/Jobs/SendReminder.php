<?php

namespace App\Jobs;

use App\Models\Reminder;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reminder;
    public $tries = 3;
    public $backoff = 60; // 1 minute between retries

    /**
     * Create a new job instance.
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsapp): void
    {
        // Reload reminder to get latest status
        $this->reminder->refresh();

        // If already sent, skip
        if ($this->reminder->status === 'sent') {
            Log::info('Reminder already sent, skipping', [
                'reminder_id' => $this->reminder->id,
            ]);
            return;
        }

        // Check if it's time to send (within 5-minute window)
        $scheduledFor = $this->reminder->scheduled_for;
        $now = now();

        if ($now->lt($scheduledFor->subMinutes(5))) {
            Log::warning('Reminder scheduled for future, re-queuing', [
                'reminder_id' => $this->reminder->id,
                'scheduled_for' => $scheduledFor,
                'current_time' => $now,
            ]);

            // Re-dispatch for the correct time
            self::dispatch($this->reminder)->delay($scheduledFor);
            return;
        }

        Log::info('Sending reminder', [
            'reminder_id' => $this->reminder->id,
            'type' => $this->reminder->reminder_type,
            'phone' => $this->reminder->phone_number,
        ]);

        try {
            $response = $whatsapp->sendTextMessage(
                $this->reminder->phone_number,
                $this->reminder->message
            );

            if ($response['success']) {
                $this->reminder->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'whatsapp_message_id' => $response['message_id'] ?? null,
                    'retry_count' => $this->attempts(),
                ]);

                Log::info('Reminder sent successfully', [
                    'reminder_id' => $this->reminder->id,
                    'message_id' => $response['message_id'] ?? null,
                ]);
            } else {
                throw new \Exception($response['error'] ?? 'Failed to send WhatsApp message');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send reminder', [
                'reminder_id' => $this->reminder->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Update retry count
            $this->reminder->update([
                'retry_count' => $this->attempts(),
                'error_message' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Reminder job failed permanently', [
            'reminder_id' => $this->reminder->id,
            'phone' => $this->reminder->phone_number,
            'type' => $this->reminder->reminder_type,
            'error' => $exception->getMessage(),
        ]);

        $this->reminder->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'retry_count' => $this->tries,
        ]);
    }
}
