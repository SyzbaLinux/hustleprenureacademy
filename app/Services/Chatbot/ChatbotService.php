<?php

namespace App\Services\Chatbot;

use App\Services\WhatsApp\FlowManager;
use App\Services\WhatsApp\MessageBuilder;
use App\Services\WhatsApp\WhatsAppService;
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

        if (! $phoneNumber) {
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
                    'Sorry, I can only process text messages and button responses.'
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
        if (! $buttonId) {
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
                    $this->eventBrowser->showEventDetails($phoneNumber, (int) $eventId);
                }
                break;

            case 'enroll':
                $eventId = $parts[2] ?? null;
                if ($eventId) {
                    $this->enrollmentService->initiateEnrollment($phoneNumber, (int) $eventId);
                }
                break;

            case 'payment':
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

            case 'back':
                // Handle 'back_to_main' button
                if (isset($parts[1]) && $parts[1] === 'to' && isset($parts[2]) && $parts[2] === 'main') {
                    $this->showMainMenu($phoneNumber);
                }
                break;

            case 'help':
                $this->showHelp($phoneNumber);
                break;

            case 'check':
                $this->enrollmentService->checkPaymentStatusManually($phoneNumber);
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
        if (! $listId) {
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
                    $this->eventBrowser->showEventsByCategory($phoneNumber, (int) $categoryId);
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
        $originalText = trim($text);
        $text = strtolower($originalText);

        // Get current flow state
        $flow = $this->flowManager->getCurrentFlow($phoneNumber);
        $currentState = $flow?->current_state ?? 'idle';

        // Handle registration states first (need original case-sensitive text)
        if ($currentState === 'registration_name') {
            $this->handleFullNameInput($phoneNumber, $originalText);

            return;
        }

        if ($currentState === 'registration_email') {
            $this->handleEmailInput($phoneNumber, $originalText);

            return;
        }

        // Handle global commands - exact matches
        if (in_array($text, ['menu', 'start'])) {
            $this->showMainMenu($phoneNumber);

            return;
        }

        // Handle greetings - starts with
        $greetings = ['hi', 'hie', 'hello', 'hey', 'hola', 'good morning', 'good afternoon', 'good evening'];
        foreach ($greetings as $greeting) {
            if (str_starts_with($text, $greeting)) {
                // Check if user exists by phone number
                $user = \App\Models\User::where('phone_number', $phoneNumber)->first();

                if (! $user) {
                    // Start registration flow - ask for full name
                    $this->startRegistrationFlow($phoneNumber);

                    return;
                }

                // Existing user - show main menu
                $this->showMainMenu($phoneNumber);

                return;
            }
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

            case 'browsing_categories':
                $this->handleCategorySelection($phoneNumber, $text);
                break;

            case 'browsing_events':
            case 'browsing_courses':
                $this->handleEventSelection($phoneNumber, $text);
                break;

            case 'awaiting_payment_number':
                $this->enrollmentService->processPaymentNumber($phoneNumber, $text);
                break;

            case 'awaiting_payment_confirmation':
                if (in_array($text, ['check', 'check status'])) {
                    $this->enrollmentService->checkPaymentStatusManually($phoneNumber);
                } else {
                    $this->whatsapp->sendTextMessage(
                        $phoneNumber,
                        "⏳ Please complete the payment on your phone. We're checking the status...\n\nThis may take up to 2 minutes.\n\n".
                        "💡 Tip: Type 'check' to manually check the payment status."
                    );
                }
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
        // Check if numeric input
        if (is_numeric($text)) {
            $option = (int) $text;

            switch ($option) {
                case 1:
                    $this->eventBrowser->showCategories($phoneNumber, 'event');
                    break;
                case 2:
                    $this->eventBrowser->showCategories($phoneNumber, 'course');
                    break;
                case 3:
                    $this->enrollmentService->showMyEnrollments($phoneNumber);
                    break;
                default:
                    $this->whatsapp->sendTextMessage(
                        $phoneNumber,
                        "Invalid option. Please reply with 1, 2, or 3.\n\nType 'menu' to see options again."
                    );
            }

            return;
        }

        // Handle text-based commands
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
     * Handle category selection in browsing_categories state
     */
    private function handleCategorySelection(string $phoneNumber, string $text): void
    {
        // Check if numeric input
        if (! is_numeric($text)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Please reply with a category number.\n\nType 'menu' to go back to the main menu."
            );

            return;
        }

        $selectedIndex = (int) $text;
        $flow = $this->flowManager->getCurrentFlow($phoneNumber);
        $categories = $flow?->getContext('categories', []);

        // Validate selection
        if ($selectedIndex < 1 || $selectedIndex > count($categories)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Invalid category number. Please select a valid option.\n\nType 'menu' to go back."
            );

            return;
        }

        // Get the category ID from the stored array (arrays are 0-indexed)
        $categoryId = $categories[$selectedIndex - 1];

        // Show events/courses for the selected category
        $this->eventBrowser->showEventsByCategory($phoneNumber, $categoryId);
    }

    /**
     * Handle event selection in browsing_events/courses state
     */
    private function handleEventSelection(string $phoneNumber, string $text): void
    {
        // Check if numeric input
        if (! is_numeric($text)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Please reply with an event number.\n\nType 'menu' to go back to the main menu."
            );

            return;
        }

        $selectedIndex = (int) $text;
        $flow = $this->flowManager->getCurrentFlow($phoneNumber);
        $events = $flow?->getContext('events', []);

        // Validate selection
        if ($selectedIndex < 1 || $selectedIndex > count($events)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                'Invalid event number. Please select a valid option (1-'.count($events).").\n\nType 'menu' to go back."
            );

            return;
        }

        // Get the event ID from the stored array (arrays are 0-indexed)
        $eventId = $events[$selectedIndex - 1];

        // Show full event details directly
        $this->eventBrowser->showEventDetails($phoneNumber, $eventId);
    }

    /**
     * Handle event action selection in selected_event state
     */
    private function handleEventActionSelection(string $phoneNumber, string $text): void
    {
        // Check if numeric input
        if (! is_numeric($text)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Please reply with an action number (1-3).\n\nType 'menu' to go back."
            );

            return;
        }

        $selectedAction = (int) $text;
        $flow = $this->flowManager->getCurrentFlow($phoneNumber);
        $eventId = $flow?->getContext('event_id');

        if (! $eventId) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Session expired. Please browse events again.\n\nType 'menu' to start over."
            );
            $this->flowManager->clearFlow($phoneNumber);

            return;
        }

        // Handle action selection
        switch ($selectedAction) {
            case 1:
                // View Full Details
                $this->eventBrowser->showEventDetails($phoneNumber, (int) $eventId);
                break;

            case 2:
                // Enroll & Pay
                $this->enrollmentService->initiateEnrollment($phoneNumber, (int) $eventId);
                break;

            case 3:
                // Back to Events - retrieve category from context
                $categoryId = $flow?->getContext('category_id');
                if ($categoryId) {
                    $this->eventBrowser->showEventsByCategory($phoneNumber, (int) $categoryId);
                } else {
                    $this->showMainMenu($phoneNumber);
                }
                break;

            default:
                $this->whatsapp->sendTextMessage(
                    $phoneNumber,
                    "Invalid option. Please reply with 1, 2, or 3.\n\nType 'menu' to go back."
                );
        }
    }

    /**
     * Show main menu with image and numbered options
     */
    public function showMainMenu(string $phoneNumber): void
    {
        // Get user name if available
        $user = \App\Models\User::where('phone_number', $phoneNumber)->first();
        $userName = $user?->name;

        $welcomeMessage = $this->messageBuilder->buildWelcomeMessage($userName);
        $imageUrl = 'https://img.freepik.com/free-photo/duck-nature-generate-image_23-2150631898.jpg';

        $this->whatsapp->sendImageMessage(
            $phoneNumber,
            $imageUrl,
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
        $message .= '📱 WhatsApp: +263 XX XXX XXXX';

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

    /**
     * Start registration flow for new user
     */
    private function startRegistrationFlow(string $phoneNumber): void
    {
        $message = "👋 *Welcome to Hustleprenure Academy!*\n\n";
        $message .= "I don't have your details yet. Let me help you create an account.\n\n";
        $message .= '📝 Please enter your *full name* as it appears on your National ID:';

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        $this->flowManager->transitionTo($phoneNumber, 'registration_name');
    }

    /**
     * Handle full name input during registration
     */
    private function handleFullNameInput(string $phoneNumber, string $fullName): void
    {
        // Validate full name - should have at least 2 words and only letters/spaces
        $fullName = trim($fullName);

        // Check if name has at least 2 parts (first and last name)
        $nameParts = preg_split('/\s+/', $fullName);
        if (count($nameParts) < 2) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Please enter your *full name* (first name and surname) as it appears on your National ID.\n\n".
                'Example: John Moyo'
            );

            return;
        }

        // Check if name contains only valid characters
        if (! preg_match('/^[a-zA-Z\s\'-]+$/', $fullName)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Please enter a valid name using only letters.\n\n".
                'Example: John Moyo'
            );

            return;
        }

        // Store the name in context and ask for email
        $this->flowManager->setContext($phoneNumber, 'registration_name', $fullName);

        $message = "Thank you, *{$fullName}*! ✅\n\n";
        $message .= '📧 Now, please enter your *email address*:';

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        $this->flowManager->transitionTo($phoneNumber, 'registration_email');
    }

    /**
     * Handle email input during registration
     */
    private function handleEmailInput(string $phoneNumber, string $email): void
    {
        $email = trim(strtolower($email));

        // Validate email format
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Please enter a valid email address.\n\n".
                'Example: john@example.com'
            );

            return;
        }

        // Check if email is already in use
        $existingUser = \App\Models\User::where('email', $email)->first();
        if ($existingUser) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                'This email is already registered. Please enter a different email address.'
            );

            return;
        }

        // Get the stored name from context
        $fullName = $this->flowManager->getContext($phoneNumber, 'registration_name');

        if (! $fullName) {
            // Something went wrong, restart registration
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Something went wrong. Let's start again."
            );
            $this->startRegistrationFlow($phoneNumber);

            return;
        }

        // Create the user account (no password since they'll use OTP)
        try {
            $user = \App\Models\User::create([
                'name' => $fullName,
                'email' => $email,
                'phone_number' => $phoneNumber,
                'password' => '', // No password - OTP authentication
                'whatsapp_verified' => true,
                'role' => \App\Enums\UserRole::User,
            ]);

            // Update last WhatsApp interaction
            $user->last_whatsapp_interaction = now();
            $user->whatsapp_verified_at = now();
            $user->save();

            // Clear the registration context
            $this->flowManager->clearFlow($phoneNumber);

            // Send welcome message
            $message = "🎉 *Account Created Successfully!*\n\n";
            $message .= "Welcome, *{$fullName}*!\n\n";
            $message .= "Your account has been created with:\n";
            $message .= "📧 Email: {$email}\n";
            $message .= "📱 Phone: {$phoneNumber}\n\n";
            $message .= "You're all set! Let me show you what we have available...";

            $this->whatsapp->sendTextMessage($phoneNumber, $message);

            // Show main menu after a short delay
            sleep(2);
            $this->showMainMenu($phoneNumber);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('User registration failed', [
                'phone' => $phoneNumber,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                'Sorry, there was an error creating your account. Please try again later or contact support.'
            );

            $this->flowManager->clearFlow($phoneNumber);
        }
    }
}
