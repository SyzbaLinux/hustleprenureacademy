<?php

namespace App\Console\Commands;

use App\Services\Chatbot\ChatbotService;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Console\Command;

class TestWhatsAppChatbot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:test {phone : The phone number to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp chatbot functionality';

    /**
     * Execute the console command.
     */
    public function handle(ChatbotService $chatbot, WhatsAppService $whatsapp): int
    {
        $phoneNumber = $this->argument('phone');

        $this->info("Testing WhatsApp Chatbot");
        $this->info("Phone Number: {$phoneNumber}");
        $this->newLine();

        $choice = $this->choice(
            'What would you like to test?',
            [
                'send_welcome' => 'Send Welcome Message',
                'send_menu' => 'Send Main Menu',
                'test_categories' => 'Test Browse Categories',
                'test_connection' => 'Test WhatsApp Connection',
                'simulate_message' => 'Simulate Incoming Text Message',
            ],
            'send_menu'
        );

        try {
            switch ($choice) {
                case 'send_welcome':
                    $this->info('Sending welcome message...');
                    $chatbot->sendWelcomeMessage($phoneNumber);
                    $this->info('✓ Welcome message sent!');
                    break;

                case 'send_menu':
                    $this->info('Sending main menu...');
                    $chatbot->showMainMenu($phoneNumber);
                    $this->info('✓ Main menu sent!');
                    break;

                case 'test_categories':
                    $eventBrowser = app(\App\Services\Chatbot\EventBrowserService::class);
                    $this->info('Sending categories list...');
                    $eventBrowser->showCategories($phoneNumber, 'event');
                    $this->info('✓ Categories sent!');
                    break;

                case 'test_connection':
                    $this->info('Testing WhatsApp API connection...');
                    $result = $whatsapp->sendTextMessage(
                        $phoneNumber,
                        "🧪 Test message from Hustleprenure Academy Chatbot\n\nConnection successful! ✅"
                    );

                    if ($result['success']) {
                        $this->info('✓ Connection test successful!');
                        $this->info("Message ID: {$result['message_id']}");
                    } else {
                        $this->error('✗ Connection test failed!');
                        $this->error("Error: {$result['error']}");
                        return self::FAILURE;
                    }
                    break;

                case 'simulate_message':
                    $text = $this->ask('Enter message text to simulate', 'menu');
                    $this->info("Simulating text message: '{$text}'");

                    $message = [
                        'from' => $phoneNumber,
                        'id' => 'test_' . uniqid(),
                        'timestamp' => time(),
                        'type' => 'text',
                        'text' => [
                            'body' => $text,
                        ],
                    ];

                    $chatbot->handleIncomingMessage($message);
                    $this->info('✓ Message processed!');
                    break;
            }

            $this->newLine();
            $this->info('Test completed successfully!');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Test failed!');
            $this->error("Error: {$e->getMessage()}");
            $this->newLine();
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
