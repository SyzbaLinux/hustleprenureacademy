<?php

namespace App\Jobs;

use App\Services\Chatbot\ChatbotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $webhookData;
    public $tries = 2;
    public $timeout = 60; // seconds

    /**
     * Create a new job instance.
     */
    public function __construct(array $webhookData)
    {
        $this->webhookData = $webhookData;
    }

    /**
     * Execute the job.
     */
    public function handle(ChatbotService $chatbot): void
    {
        try {
            // Process each entry
            foreach ($this->webhookData['entry'] ?? [] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    if ($change['field'] !== 'messages') {
                        continue;
                    }

                    $value = $change['value'] ?? [];

                    // Process each message
                    foreach ($value['messages'] ?? [] as $message) {
                        $this->processMessage($chatbot, $message);
                    }

                    // Process status updates (delivery, read receipts, etc.)
                    foreach ($value['statuses'] ?? [] as $status) {
                        $this->processStatus($status);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Process individual message
     */
    private function processMessage(ChatbotService $chatbot, array $message): void
    {
        $messageType = $message['type'] ?? null;

        // Skip unsupported message types
        if (in_array($messageType, ['audio', 'document', 'image', 'video', 'sticker', 'location', 'contacts'])) {
            Log::info('Skipping unsupported message type', [
                'type' => $messageType,
                'from' => $message['from'] ?? null,
            ]);
            return;
        }

        Log::info('Processing message', [
            'type' => $messageType,
            'from' => $message['from'] ?? null,
            'message_id' => $message['id'] ?? null,
        ]);

        try {
            $chatbot->handleIncomingMessage($message);
        } catch (\Exception $e) {
            Log::error('Error handling message', [
                'message_id' => $message['id'] ?? null,
                'from' => $message['from'] ?? null,
                'error' => $e->getMessage(),
            ]);

            // Don't re-throw, continue processing other messages
        }
    }

    /**
     * Process status update
     */
    private function processStatus(array $status): void
    {
        Log::debug('Message status update', [
            'message_id' => $status['id'] ?? null,
            'status' => $status['status'] ?? null,
            'recipient' => $status['recipient_id'] ?? null,
        ]);

        // You can add logic here to track message delivery/read status
        // For now, just log it
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp webhook processing job failed', [
            'error' => $exception->getMessage(),
            'data' => $this->webhookData,
        ]);
    }
}
