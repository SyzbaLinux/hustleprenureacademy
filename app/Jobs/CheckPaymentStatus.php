<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\Payment\PesePayService;
use App\Services\Chatbot\EnrollmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payment;
    public $tries = 10;
    public $backoff = 3; // seconds between retries

    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(PesePayService $pesePayService, EnrollmentService $enrollmentService): void
    {
        // Reload payment to get latest status
        $this->payment->refresh();

        // If payment already processed, no need to check
        if (in_array($this->payment->status, ['paid', 'failed', 'refunded'])) {
            Log::info('Payment already processed, skipping check', [
                'payment_id' => $this->payment->id,
                'status' => $this->payment->status,
            ]);
            return;
        }

        Log::info('Checking payment status', [
            'payment_id' => $this->payment->id,
            'reference' => $this->payment->reference_number,
            'attempt' => $this->attempts(),
        ]);

        $result = $pesePayService->checkPaymentStatus($this->payment->reference_number);

        if ($result['paid']) {
            // Payment successful
            $this->payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'pesepay_response' => array_merge(
                    $this->payment->pesepay_response ?? [],
                    [
                        'poll_result' => $result,
                        'confirmed_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            Log::info('Payment confirmed as paid', [
                'payment_id' => $this->payment->id,
                'reference' => $this->payment->reference_number,
            ]);

            // Complete enrollment
            $enrollmentService->completeEnrollment($this->payment);

        } elseif ($result['status'] === 'failed') {
            // Payment failed
            $this->payment->update([
                'status' => 'failed',
                'failed_reason' => $result['transaction_status'] ?? 'Payment failed',
                'pesepay_response' => array_merge(
                    $this->payment->pesepay_response ?? [],
                    [
                        'poll_result' => $result,
                        'failed_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            Log::warning('Payment confirmed as failed', [
                'payment_id' => $this->payment->id,
                'reference' => $this->payment->reference_number,
                'reason' => $result['transaction_status'],
            ]);

            // Notify user of failure
            $enrollmentService->handleFailedPayment($this->payment);

        } else {
            // Still pending, retry if attempts remaining
            if ($this->attempts() < $this->tries) {
                Log::info('Payment still pending, will retry', [
                    'payment_id' => $this->payment->id,
                    'attempt' => $this->attempts(),
                    'max_tries' => $this->tries,
                ]);

                // Update status to processing
                if ($this->payment->status === 'pending') {
                    $this->payment->update(['status' => 'processing']);
                }

                // Re-throw to trigger retry
                throw new \Exception('Payment still pending, retrying...');
            } else {
                // Max retries exhausted
                Log::warning('Payment status check timeout', [
                    'payment_id' => $this->payment->id,
                    'reference' => $this->payment->reference_number,
                    'attempts' => $this->attempts(),
                ]);

                // Keep as pending, but notify user
                $this->payment->update([
                    'pesepay_response' => array_merge(
                        $this->payment->pesepay_response ?? [],
                        [
                            'timeout' => true,
                            'max_attempts_reached' => $this->tries,
                        ]
                    ),
                ]);

                // Send timeout notification
                $this->sendTimeoutNotification();
            }
        }
    }

    /**
     * Send notification when payment check times out
     */
    private function sendTimeoutNotification(): void
    {
        try {
            $whatsapp = app(\App\Services\WhatsApp\WhatsAppService::class);

            $message = "⏱️ *Payment Status Update*\n\n";
            $message .= "We're still waiting for confirmation of your payment.\n\n";
            $message .= "Reference: {$this->payment->reference_number}\n";
            $message .= "Amount: \${$this->payment->amount}\n\n";
            $message .= "If you've completed the payment, it may take a few more minutes to reflect.\n\n";
            $message .= "If you're experiencing issues, please contact support:\n";
            $message .= "📧 support@hustleprenureacademy.com";

            $whatsapp->sendTextMessage($this->payment->phone_number, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send timeout notification', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Payment status check job failed permanently', [
            'payment_id' => $this->payment->id,
            'reference' => $this->payment->reference_number,
            'error' => $exception->getMessage(),
        ]);

        // Update payment with error
        $this->payment->update([
            'pesepay_response' => array_merge(
                $this->payment->pesepay_response ?? [],
                [
                    'job_failed' => true,
                    'error' => $exception->getMessage(),
                ]
            ),
        ]);
    }
}
