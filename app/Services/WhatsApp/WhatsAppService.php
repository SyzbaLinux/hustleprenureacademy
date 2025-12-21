<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $graphApiToken;

    protected $businessPhoneNumberId;

    protected $apiVersion;

    public function __construct()
    {
        $this->graphApiToken = config('whatsapp.graph_api_token');
        $this->businessPhoneNumberId = config('whatsapp.business_phone_number_id');
        $this->apiVersion = config('whatsapp.api_version', 'v22.0');
    }

    /**
     * Send a text message
     */
    public function sendTextMessage(string $to, string $message): array
    {
        try {
            $response = Http::withToken($this->graphApiToken)
                ->post($this->getApiUrl().'/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('WhatsApp text message failed', [
                    'to' => $to,
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send interactive buttons
     */
    public function sendInteractiveButtons(string $to, array $buttons, string $body, ?array $header = null, ?string $footer = null): array
    {
        try {
            $interactive = [
                'type' => 'button',
                'body' => ['text' => $body],
                'action' => [
                    'buttons' => $buttons,
                ],
            ];

            if ($header) {
                $interactive['header'] = $header;
            }

            if ($footer) {
                $interactive['footer'] = ['text' => $footer];
            }

            $response = Http::withToken($this->graphApiToken)
                ->post($this->getApiUrl().'/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'interactive',
                    'interactive' => $interactive,
                ]);

            if (! $response->successful()) {
                Log::error('WhatsApp interactive buttons failed', [
                    'to' => $to,
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send interactive list
     */
    public function sendInteractiveList(string $to, array $sections, string $body, string $buttonText = 'Select', ?array $header = null): array
    {
        try {
            $interactive = [
                'type' => 'list',
                'body' => ['text' => $body],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections,
                ],
            ];

            if ($header) {
                $interactive['header'] = $header;
            }

            $response = Http::withToken($this->graphApiToken)
                ->post($this->getApiUrl().'/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'interactive',
                    'interactive' => $interactive,
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp interactive list sent', [
                    'to' => $to,
                    'message_id' => $response->json('messages.0.id'),
                ]);
            } else {
                Log::error('WhatsApp interactive list failed', [
                    'to' => $to,
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an image message with optional caption
     */
    public function sendImageMessage(string $to, string $imageUrl, ?string $caption = null): array
    {
        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'image',
                'image' => [
                    'link' => $imageUrl,
                ],
            ];

            if ($caption) {
                $payload['image']['caption'] = $caption;
            }

            $response = Http::withToken($this->graphApiToken)
                ->post($this->getApiUrl().'/messages', $payload);

            if ($response->successful()) {
                Log::info('WhatsApp image message sent', [
                    'to' => $to,
                    'message_id' => $response->json('messages.0.id'),
                ]);
            } else {
                Log::error('WhatsApp image message failed', [
                    'to' => $to,
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Mark message as read
     */
    public function markMessageAsRead(string $messageId): bool
    {
        try {
            $response = Http::withToken($this->graphApiToken)
                ->post($this->getApiUrl().'/messages', [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to mark message as read', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the API URL
     */
    protected function getApiUrl(): string
    {
        return "https://graph.facebook.com/{$this->apiVersion}/{$this->businessPhoneNumberId}";
    }
}
