<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Jobs\ProcessWhatsAppWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify webhook token
     * GET /webhook
     */
    public function verify(Request $request)
    {
        $verifyToken = env('WEBHOOK_VERIFY_TOKEN', 'your_verify_token');
        $challenge = $request->query('hub_challenge');
        $token = $request->query('hub_verify_token');

        if ($token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Invalid token', 403);
    }

    /**
     * Handle incoming WhatsApp messages
     * POST /webhook
     */
    public function handle(Request $request)
    {
        try {
            $data = $request->json()->all();

            // Handle status updates
            if ($this->isStatusUpdate($data)) {
                return $this->handleStatusUpdate($data);
            }

            // Handle incoming messages
            if ($this->isIncomingMessage($data)) {
                return $this->handleIncomingMessage($data);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('OK', 200);
        }
    }

    /**
     * Check if request is a status update
     */
    private function isStatusUpdate(array $data): bool
    {
        return isset($data['entry'][0]['changes'][0]['value']['statuses']);
    }

    /**
     * Check if request is an incoming message
     */
    private function isIncomingMessage(array $data): bool
    {
        return isset($data['entry'][0]['changes'][0]['value']['messages'][0]);
    }

    /**
     * Handle status updates (delivery, read, failed)
     */
    private function handleStatusUpdate(array $data)
    {
        try {
            $status = $data['entry'][0]['changes'][0]['value']['statuses'][0];
            $messageId = $status['id'];
            $statusType = $status['status']; // sent, delivered, read, failed
            $phoneNumber = $status['recipient_id'] ?? null;

            Log::info('WhatsApp message status update', [
                'message_id' => $messageId,
                'status' => $statusType,
                'phone_number' => $phoneNumber,
            ]);

            // You can update your message log/delivery status here
            // For now, just log it

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error handling WhatsApp status update', [
                'error' => $e->getMessage(),
            ]);

            return response('OK', 200);
        }
    }

    /**
     * Handle incoming messages
     */
    private function handleIncomingMessage(array $data)
    {
        try {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $phoneNumber = $message['from'];
            $messageId = $message['id'];
            $type = $message['type']; // text, image, document, etc.

            Log::info('WhatsApp message received', [
                'phone_number' => $phoneNumber,
                'message_id' => $messageId,
                'type' => $type,
            ]);

            $isOTPMessage = false;

            // Check if it's an OTP message (text with 6 digits)
            if ($type === 'text') {
                $messageText = $message['text']['body'];

                // Extract OTP code (6 digits)
                if (preg_match('/\b(\d{6})\b/', $messageText, $matches)) {
                    $otpCode = $matches[1];

                    // Try to verify the OTP
                    if ($this->processOTPFromMessage($phoneNumber, $otpCode)) {
                        $isOTPMessage = true;

                        // Mark message as read
                        $this->markMessageAsRead($messageId);
                    }
                }
            }

            // If not an OTP message, route to chatbot
            if (!$isOTPMessage) {
                // Queue webhook processing to avoid blocking response
                ProcessWhatsAppWebhook::dispatch($data);

                Log::info('WhatsApp message queued for chatbot processing', [
                    'phone_number' => $phoneNumber,
                    'message_id' => $messageId,
                ]);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error handling WhatsApp incoming message', [
                'error' => $e->getMessage(),
            ]);

            return response('OK', 200);
        }
    }

    /**
     * Process OTP from WhatsApp message
     * Returns true if OTP was successfully verified
     */
    private function processOTPFromMessage(string $phoneNumber, string $otpCode): bool
    {
        try {
            // Try to verify the OTP for 'login' type
            $otp = Otp::verify($phoneNumber, $otpCode, 'login');

            if ($otp) {
                Log::info('OTP verified from WhatsApp message', [
                    'phone_number' => $phoneNumber,
                    'otp_id' => $otp->id,
                ]);

                // Optional: Send confirmation message
                $this->sendConfirmationMessage($phoneNumber, 'Your identity has been verified! You can close this window.');

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error processing OTP from WhatsApp message', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark message as read
     */
    private function markMessageAsRead(string $messageId)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withToken(env('GRAPH_API_TOKEN'))
                ->post('https://graph.facebook.com/v22.0/' . env('BUSINESS_PHONE_NUMBER_ID') . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId,
                ]);

            if (!$response->successful()) {
                Log::warning('Failed to mark WhatsApp message as read', [
                    'message_id' => $messageId,
                    'status' => $response->status(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error marking message as read', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send confirmation message
     */
    private function sendConfirmationMessage(string $phoneNumber, string $message)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withToken(env('GRAPH_API_TOKEN'))
                ->post('https://graph.facebook.com/v22.0/' . env('BUSINESS_PHONE_NUMBER_ID') . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('Confirmation message sent', [
                    'phone_number' => $phoneNumber,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending confirmation message', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
