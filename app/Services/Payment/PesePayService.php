<?php

namespace App\Services\Payment;

use App\Models\Event;
use App\Models\Payment;
use Emmanuelsiziba\Payments\Pesepay;
use Illuminate\Support\Facades\Log;

class PesePayService
{
    protected $pesepay;

    public function __construct()
    {
        $this->pesepay = new Pesepay(
            config('services.pesepay.int_key'),
            config('services.pesepay.ency_key')
        );

        $this->pesepay->returnUrl = config('services.pesepay.return_url');
        $this->pesepay->resultUrl = config('services.pesepay.result_url');
    }

    /**
     * Create a seamless payment
     */
    public function createSeamlessPayment(Event $event, string $phoneNumber, string $paymentMethod = 'mobile_money'): array
    {
        try {
            $requiredFields = ['customerPhoneNumber' => $phoneNumber];

            $payment = $this->pesepay->createPayment(
                config('services.pesepay.currency', 'USD'),
                config('services.pesepay.merchant_code', 'PZW211'),
                'payment@hustleprenureacademy.com',
                $event->title,
                $event->title
            );

            $response = $this->pesepay->makeSeamlessPayment(
                $payment,
                'Payment for ' . $event->title,
                $event->amount,
                $requiredFields,
                $event->title
            );

            if ($response->success()) {
                $paymentRecord = Payment::create([
                    'event_id' => $event->id,
                    'phone_number' => $phoneNumber,
                    'amount' => $event->amount,
                    'currency' => $event->currency,
                    'payment_method' => $paymentMethod,
                    'reference_number' => $response->referenceNumber(),
                    'poll_url' => $response->pollUrl(),
                    'status' => 'pending',
                    'pesepay_response' => [
                        'success' => true,
                        'reference' => $response->referenceNumber(),
                        'poll_url' => $response->pollUrl(),
                    ],
                ]);

                Log::info('PesePay payment initiated', [
                    'event_id' => $event->id,
                    'reference' => $response->referenceNumber(),
                    'amount' => $event->amount,
                ]);

                return [
                    'success' => true,
                    'payment' => $paymentRecord,
                    'reference_number' => $response->referenceNumber(),
                    'poll_url' => $response->pollUrl(),
                    'message' => 'Please enter your PIN on your phone to complete payment.',
                ];
            } else {
                $errorMessage = $response->message();

                Log::error('PesePay payment failed', [
                    'event_id' => $event->id,
                    'error' => $errorMessage,
                ]);

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'message' => 'Payment initiation failed. Please try again.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('PesePay service exception', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred while processing payment.',
            ];
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $referenceNumber): array
    {
        try {
            $response = $this->pesepay->checkPayment($referenceNumber);

            $status = 'pending';
            $paid = false;

            if ($response->success()) {
                if ($response->paid()) {
                    $status = 'paid';
                    $paid = true;
                } elseif ($response->transactionStatus() === 'FAILED') {
                    $status = 'failed';
                }
            }

            return [
                'success' => $response->success(),
                'paid' => $paid,
                'status' => $status,
                'transaction_status' => $response->transactionStatus(),
                'reference_number' => $referenceNumber,
                'response' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('PesePay check payment exception', [
                'reference' => $referenceNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'paid' => false,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Poll payment status (with retries)
     */
    public function pollPaymentStatus(Payment $payment, int $maxRetries = 10, int $interval = 3): bool
    {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $result = $this->checkPaymentStatus($payment->reference_number);

            Log::info("Payment poll attempt {$attempt}/{$maxRetries}", [
                'reference' => $payment->reference_number,
                'status' => $result['status'],
            ]);

            if ($result['paid']) {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'pesepay_response' => array_merge(
                        $payment->pesepay_response ?? [],
                        ['poll_result' => $result]
                    ),
                ]);

                return true;
            }

            if ($result['status'] === 'failed') {
                $payment->update([
                    'status' => 'failed',
                    'failed_reason' => $result['transaction_status'] ?? 'Payment failed',
                    'pesepay_response' => array_merge(
                        $payment->pesepay_response ?? [],
                        ['poll_result' => $result]
                    ),
                ]);

                return false;
            }

            if ($attempt < $maxRetries) {
                sleep($interval);
            }
        }

        // Max retries exhausted, still pending
        Log::warning('Payment polling timeout', [
            'reference' => $payment->reference_number,
            'attempts' => $maxRetries,
        ]);

        return false;
    }

    /**
     * Handle webhook callback
     */
    public function handleWebhookCallback(array $data): void
    {
        try {
            $referenceNumber = $data['referenceNumber'] ?? null;

            if (!$referenceNumber) {
                Log::warning('PesePay webhook missing reference number', $data);
                return;
            }

            $payment = Payment::where('reference_number', $referenceNumber)->first();

            if (!$payment) {
                Log::warning('PesePay webhook for unknown payment', [
                    'reference' => $referenceNumber,
                ]);
                return;
            }

            $result = $this->checkPaymentStatus($referenceNumber);

            if ($result['paid']) {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'transaction_id' => $data['transactionId'] ?? null,
                    'pesepay_response' => array_merge(
                        $payment->pesepay_response ?? [],
                        ['webhook' => $data]
                    ),
                ]);

                Log::info('Payment marked as paid via webhook', [
                    'reference' => $referenceNumber,
                    'payment_id' => $payment->id,
                ]);
            } elseif ($result['status'] === 'failed') {
                $payment->update([
                    'status' => 'failed',
                    'failed_reason' => $result['transaction_status'] ?? 'Payment failed',
                    'pesepay_response' => array_merge(
                        $payment->pesepay_response ?? [],
                        ['webhook' => $data]
                    ),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PesePay webhook processing error', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }
}
