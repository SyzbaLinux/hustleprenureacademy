<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Check if status changed to 'paid'
        if ($payment->isDirty('status') && $payment->status === 'paid') {
            Log::info('Payment status changed to paid', [
                'payment_id' => $payment->id,
                'event_id' => $payment->event_id,
            ]);

            // Check if enrollment already exists
            $existingEnrollment = Enrollment::where('payment_id', $payment->id)->first();

            if ($existingEnrollment) {
                Log::info('Enrollment already exists for this payment', [
                    'payment_id' => $payment->id,
                    'enrollment_id' => $existingEnrollment->id,
                ]);
                return;
            }

            // Note: The actual enrollment creation is handled by EnrollmentService->completeEnrollment()
            // which is called from CheckPaymentStatus job
            // This observer is just for logging and any additional processing needed
        }

        // Handle failed payment
        if ($payment->isDirty('status') && $payment->status === 'failed') {
            Log::warning('Payment failed', [
                'payment_id' => $payment->id,
                'event_id' => $payment->event_id,
                'reason' => $payment->failed_reason,
            ]);

            // Note: Failure notification is handled by EnrollmentService->handleFailedPayment()
            // which is called from CheckPaymentStatus job
        }

        // Handle refunded payment
        if ($payment->isDirty('status') && $payment->status === 'refunded') {
            Log::info('Payment refunded', [
                'payment_id' => $payment->id,
            ]);

            // Find and cancel related enrollment
            $enrollment = Enrollment::where('payment_id', $payment->id)->first();

            if ($enrollment && $enrollment->status !== 'cancelled') {
                try {
                    $enrollment->update(['status' => 'cancelled']);

                    Log::info('Enrollment cancelled due to refund', [
                        'enrollment_id' => $enrollment->id,
                        'payment_id' => $payment->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to cancel enrollment after refund', [
                        'enrollment_id' => $enrollment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
