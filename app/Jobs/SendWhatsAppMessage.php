<?php

namespace App\Jobs;

use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phoneNumber;

    public $message;

    public $messageType;

    public $additionalData;

    public $tries = 3;

    public $backoff = 10; // seconds between retries

    /**
     * Create a new job instance.
     *
     * @param  string|array  $message
     * @param  string  $messageType  - 'text', 'buttons', 'list'
     * @param  array  $additionalData  - Additional data for buttons/lists
     */
    public function __construct(
        string $phoneNumber,
        $message,
        string $messageType = 'text',
        array $additionalData = []
    ) {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->messageType = $messageType;
        $this->additionalData = $additionalData;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsapp): void
    {
        try {
            $response = [];

            switch ($this->messageType) {
                case 'text':
                    $response = $whatsapp->sendTextMessage($this->phoneNumber, $this->message);
                    break;

                case 'buttons':
                    $buttons = $this->additionalData['buttons'] ?? [];
                    $body = is_string($this->message) ? $this->message : ($this->message['body'] ?? '');
                    $header = $this->additionalData['header'] ?? null;

                    $response = $whatsapp->sendInteractiveButtons(
                        $this->phoneNumber,
                        $buttons,
                        $body,
                        $header
                    );
                    break;

                case 'list':
                    $sections = $this->additionalData['sections'] ?? [];
                    $body = is_string($this->message) ? $this->message : ($this->message['body'] ?? '');
                    $buttonText = $this->additionalData['button_text'] ?? 'Select Option';

                    $response = $whatsapp->sendInteractiveList(
                        $this->phoneNumber,
                        $sections,
                        $body,
                        $buttonText
                    );
                    break;

                default:
                    throw new \Exception("Unsupported message type: {$this->messageType}");
            }

            if (! $response['success']) {
                throw new \Exception($response['error'] ?? 'Failed to send message');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'phone' => $this->phoneNumber,
                'type' => $this->messageType,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp message job failed permanently', [
            'phone' => $this->phoneNumber,
            'type' => $this->messageType,
            'error' => $exception->getMessage(),
        ]);
    }
}
