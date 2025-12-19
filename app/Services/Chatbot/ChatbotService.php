<?php

namespace App\Services\Chatbot;

use App\Services\WhatsApp\WhatsAppService;
use App\Services\WhatsApp\MessageBuilder;
use App\Services\WhatsApp\FlowManager;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $whatsapp;
    protected $messageBuilder;
    protected $flowManager;
    protected $eventBrowser;
    protected $enrollmentService;

    public function __construct(
        WhatsAppService $whatsapp,
        MessageBuilder $messageBuilder,
        FlowManager $flowManager,
        EventBrowserService $eventBrowser,
        EnrollmentService $enrollmentService
    ) {
        $this->whatsapp = $whatsapp;
        $this->messageBuilder = $messageBuilder;
        $this->flowManager = $flowManager;
        $this->eventBrowser = $eventBrowser;
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Handle incoming WhatsApp message
     */
    public function handleIncomingMessage(array $message): void
    {
        $phoneNumber = $message['from'] ?? null;
        $messageId = $message['id'] ?? null;

        if (!$phoneNumber) {
            Log::warning('WhatsApp message missing phone number', $message);
            return;
        }

        // Mark message as read
        if ($messageId) {
            $this->whatsapp->markMessageAsRead($messageId);
        }

        $messageType = $message['type'] ?? 'text';

        try {
            if ($messageType === 'interactive') {
                $this->handleInteractiveResponse($phoneNumber, $message['interactive']);
            } elseif ($messageType === 'text') {
                $this->handleTextMessage($phoneNumber, $message['text']['body'] ?? '');
            } else {
                $this->whatsapp->sendTextMessage(
                    $phoneNumber,
                    "Sorry, I can only process text messages and button responses."
                );
            }
        } catch (\Exception $e) {
            Log::error('Chatbot error', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, something went wrong. Please try again or type 'menu' to start over."
            );
        }
    }

    /**
     * Handle interactive button/list response
     */
    private function handleInteractiveResponse(string $phoneNumber, array $interactive): void
    {
        $type = $interactive['type'] ?? null;

        if ($type === 'button_reply') {
            $buttonId = $interactive['button_reply']['id'] ?? null;
            $this->handleButtonResponse($phoneNumber, $buttonId);
        } elseif ($type === 'list_reply') {
            $listId = $interactive['list_reply']['id'] ?? null;
            $this->handleListResponse($phoneNumber, $listId);
        }
    }

    /**
     * Handle button response
     */
    private function handleButtonResponse(string $phoneNumber, ?string $buttonId): void
    {
        if (!$buttonId) {
            return;
        }

        Log::info('Button clicked', ['phone' => $phoneNumber, 'button_id' => $buttonId]);

        // Parse button ID format: {action}_{entity}_{id}
        $parts = explode('_', $buttonId);
        $action = $parts[0] ?? null;

        switch ($action) {
            case 'browse':
                $type = $parts[1] ?? 'event'; // events or courses
                $this->eventBrowser->showCategories($phoneNumber, $type);
                break;

            case 'view':
                $eventId = $parts[2] ?? null;
                if ($eventId) {
                    $this->eventBrowser->showEventDetails($phoneNumber, (int)$eventId);
                }
                break;

            case 'enroll':
                $eventId = $parts[1] ?? null;
                if ($eventId) {
                    $this->enrollmentService->initiateEnrollment($phoneNumber, (int)$eventId);
                }
                break;

            case 'pay':
                $paymentMethod = $parts[1] ?? 'ecocash';
                $this->enrollmentService->processPaymentMethodSelection($phoneNumber, $paymentMethod);
                break;

            case 'my':
                // Handle 'my_enrollments' button
                if (isset($parts[1]) && $parts[1] === 'enrollments') {
                    $this->enrollmentService->showMyEnrollments($phoneNumber);
                }
                break;

            case 'enrollments':
                $this->enrollmentService->showMyEnrollments($phoneNumber);
                break;

            case 'menu':
                $this->showMainMenu($phoneNumber);
                break;

            case 'help':
                $this->showHelp($phoneNumber);
                break;

            default:
                $this->whatsapp->sendTextMessage(
                    $phoneNumber,
                    "Sorry, I didn't understand that action. Type 'menu' to see available options."
                );
        }
    }

    /**
     * Handle list selection response
     */
    private function handleListResponse(string $phoneNumber, ?string $listId): void
    {
        if (!$listId) {
            return;
        }

        Log::info('List item selected', ['phone' => $phoneNumber, 'list_id' => $listId]);

        // Parse list ID format: {action}_{entity}_{id}
        $parts = explode('_', $listId);
        $action = $parts[0] ?? null;

        switch ($action) {
            case 'category':
                $categoryId = $parts[1] ?? null;
                if ($categoryId) {
                    $this->eventBrowser->showEventsByCategory($phoneNumber, (int)$categoryId);
                }
                break;

            default:
                $this->whatsapp->sendTextMessage(
                    $phoneNumber,
                    "Sorry, I didn't understand that selection. Type 'menu' to start over."
                );
        }
    }

    /**
     * Handle text message
     */
    private function handleTextMessage(string $phoneNumber, string $text): void
    {
        $text = trim(strtolower($text));

        // Get current flow state
        $flow = $this->flowManager->getCurrentFlow($phoneNumber);
        $currentState = $flow?->current_state ?? 'idle';

        // Handle global commands
        if (in_array($text, ['menu', 'start', 'hi', 'hello', 'hey'])) {
            $this->showMainMenu($phoneNumber);
            return;
        }

        if (in_array($text, ['help', '?'])) {
            $this->showHelp($phoneNumber);
            return;
        }

        // Handle state-specific text input
        switch ($currentState) {
            case 'idle':
            case 'main_menu':
                $this->handleMainMenuText($phoneNumber, $text);
                break;

            case 'awaiting_payment_number':
                $this->enrollmentService->processPaymentNumber($phoneNumber, $text);
                break;

            case 'awaiting_payment_confirmation':
                $this->whatsapp->sendTextMessage(
                    $phoneNumber,
                    "⏳ Please complete the payment on your phone. We're checking the status...\n\nThis may take up to 2 minutes."
                );
                break;

            default:
                $this->whatsapp->sendTextMessage(
                    $phoneNumber,
                    "I'm not sure what you mean. Type 'menu' to see available options or 'help' for assistance."
                );
        }
    }

    /**
     * Handle text commands in main menu state
     */
    private function handleMainMenuText(string $phoneNumber, string $text): void
    {
        if (str_contains($text, 'event')) {
            $this->eventBrowser->showCategories($phoneNumber, 'event');
        } elseif (str_contains($text, 'course')) {
            $this->eventBrowser->showCategories($phoneNumber, 'course');
        } elseif (str_contains($text, 'enrollment') || str_contains($text, 'my')) {
            $this->enrollmentService->showMyEnrollments($phoneNumber);
        } else {
            $this->showMainMenu($phoneNumber);
        }
    }

    /**
     * Show main menu
     */
    public function showMainMenu(string $phoneNumber): void
    {
        $buttons = $this->messageBuilder->buildMainMenu();
        $welcomeMessage = $this->messageBuilder->buildWelcomeMessage();

        $this->whatsapp->sendInteractiveButtons(
            $phoneNumber,
            $buttons,
            $welcomeMessage
        );

        $this->flowManager->transitionTo($phoneNumber, 'main_menu');
    }

    /**
     * Show help message
     */
    private function showHelp(string $phoneNumber): void
    {
        $message = "📖 *Help & Support*\n\n";
        $message .= "Here's what I can help you with:\n\n";
        $message .= "🔹 *Browse Events* - View upcoming events and workshops\n";
        $message .= "🔹 *Browse Courses* - Explore multi-session courses\n";
        $message .= "🔹 *Enroll & Pay* - Register and pay via mobile money\n";
        $message .= "🔹 *My Enrollments* - View your active enrollments\n\n";
        $message .= "*Quick Commands:*\n";
        $message .= "• Type 'menu' or 'start' - Show main menu\n";
        $message .= "• Type 'help' - Show this help message\n";
        $message .= "• Type 'events' - Browse events\n";
        $message .= "• Type 'courses' - Browse courses\n";
        $message .= "• Type 'my enrollments' - View enrollments\n\n";
        $message .= "💡 *How to Enroll:*\n";
        $message .= "1. Browse events or courses\n";
        $message .= "2. Select one to view details\n";
        $message .= "3. Click 'Enroll & Pay'\n";
        $message .= "4. Choose payment method\n";
        $message .= "5. Enter your mobile money number\n";
        $message .= "6. Complete payment on your phone\n\n";
        $message .= "Need more help? Contact us:\n";
        $message .= "📧 support@hustleprenureacademy.com\n";
        $message .= "📱 WhatsApp: +263 XX XXX XXXX";

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        $this->flowManager->transitionTo($phoneNumber, 'viewing_help');
    }

    /**
     * Send welcome message to new user
     */
    public function sendWelcomeMessage(string $phoneNumber): void
    {
        $message = "👋 *Welcome to Hustleprenure Academy!*\n\n";
        $message .= "We're excited to have you here! 🎓\n\n";
        $message .= "I'm your personal assistant to help you:\n";
        $message .= "✅ Browse events and courses\n";
        $message .= "✅ Enroll and pay securely\n";
        $message .= "✅ Get reminders and updates\n\n";
        $message .= "Let's get started! Type 'menu' to see what's available.";

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        // Show main menu after welcome
        sleep(2);
        $this->showMainMenu($phoneNumber);
    }
}
