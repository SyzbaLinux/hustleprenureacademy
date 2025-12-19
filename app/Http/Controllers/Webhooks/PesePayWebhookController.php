<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payment\PesePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PesePayWebhookController extends Controller
{
    protected $pesePayService;

    public function __construct(PesePayService $pesePayService)
    {
        $this->pesePayService = $pesePayService;
    }

    /**
     * Handle return callback from PesePay
     * This is called when user completes/cancels payment
     *
     * GET /webhooks/pesepay/return
     */
    public function handleReturn(Request $request)
    {
        try {
            $referenceNumber = $request->query('referenceNumber');
            $transactionId = $request->query('transactionId');
            $pollUrl = $request->query('pollUrl');

            Log::info('PesePay return callback', [
                'reference' => $referenceNumber,
                'transaction_id' => $transactionId,
                'poll_url' => $pollUrl,
                'all_params' => $request->all(),
            ]);

            if ($referenceNumber) {
                // Check payment status
                $result = $this->pesePayService->checkPaymentStatus($referenceNumber);

                Log::info('Payment status from return callback', [
                    'reference' => $referenceNumber,
                    'status' => $result['status'],
                    'paid' => $result['paid'],
                ]);
            }

            // Redirect to a thank you page or show payment status
            return view('payment.return', [
                'referenceNumber' => $referenceNumber,
                'transactionId' => $transactionId,
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling PesePay return callback', [
                'error' => $e->getMessage(),
                'params' => $request->all(),
            ]);

            return view('payment.error', [
                'message' => 'Unable to verify payment status. Please contact support.',
            ]);
        }
    }

    /**
     * Handle result callback from PesePay
     * This is called by PesePay server when payment is completed
     *
     * POST /webhooks/pesepay/result
     */
    public function handleResult(Request $request)
    {
        try {
            $data = $request->all();

            Log::info('PesePay result callback', [
                'data' => $data,
            ]);

            // Process the webhook
            $this->pesePayService->handleWebhookCallback($data);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error handling PesePay result callback', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            // Still return 200 to prevent retries
            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook',
            ], 200);
        }
    }
}
