<?php

namespace App\Services\Chatbot;

use App\Models\Event;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\WhatsApp\MessageBuilder;
use App\Services\WhatsApp\FlowManager;
use App\Services\Payment\PesePayService;
use App\Jobs\CheckPaymentStatus;
use Illuminate\Support\Facades\Log;

class EnrollmentService
{
    protected $whatsapp;
    protected $messageBuilder;
    protected $flowManager;
    protected $pesePayService;

    public function __construct(
        WhatsAppService $whatsapp,
        MessageBuilder $messageBuilder,
        FlowManager $flowManager,
        PesePayService $pesePayService
    ) {
        $this->whatsapp = $whatsapp;
        $this->messageBuilder = $messageBuilder;
        $this->flowManager = $flowManager;
        $this->pesePayService = $pesePayService;
    }

    /**
     * Initiate enrollment process for an event
     */
    public function initiateEnrollment(string $phoneNumber, int $eventId): void
    {
        $event = Event::with(['category', 'instructors'])->find($eventId);

        if (!$event) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, this event/course is no longer available."
            );
            return;
        }

        // Check if event is active and published
        if (!$event->is_active || !$event->published_at) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, this {$event->type} is not currently available for enrollment."
            );
            return;
        }

        // Check capacity
        if ($event->capacity) {
            $enrolledCount = $event->enrollments()->confirmed()->count();
            if ($enrolledCount >= $event->capacity) {
                $this->whatsapp->sendTextMessage(
                    $phoneNumber,
                    "⚠️ Sorry, this {$event->type} is fully booked. No spots available."
                );
                return;
            }
        }

        // Check if user already enrolled
        $existingEnrollment = Enrollment::where('phone_number', $phoneNumber)
            ->where('event_id', $eventId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->first();

        if ($existingEnrollment) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "You are already enrolled in this {$event->type}. Check 'My Enrollments' to view details."
            );
            return;
        }

        // Show payment method selection
        $buttons = $this->messageBuilder->buildPaymentMethodButtons();

        $message = "💳 *Payment Required*\n\n";
        $message .= "Event: {$event->title}\n";
        $message .= "Amount: \${$event->amount}\n\n";
        $message .= "Please select your payment method:";

        $this->whatsapp->sendInteractiveButtons(
            $phoneNumber,
            $buttons,
            $message
        );

        $this->flowManager->transitionTo($phoneNumber, 'selecting_payment_method', [
            'event_id' => $eventId,
        ]);
    }

    /**
     * Process payment method selection
     */
    public function processPaymentMethodSelection(string $phoneNumber, string $paymentMethod): void
    {
        $eventId = $this->flowManager->getContext($phoneNumber, 'event_id');

        if (!$eventId) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Session expired. Please start again by browsing events."
            );
            $this->flowManager->clearFlow($phoneNumber);
            return;
        }

        $event = Event::find($eventId);

        if (!$event) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, this event is no longer available."
            );
            $this->flowManager->clearFlow($phoneNumber);
            return;
        }

        // Request payment phone number
        $this->whatsapp->sendTextMessage(
            $phoneNumber,
            "📱 Please send the phone number you want to use for payment.\n\nExample: 0771234567"
        );

        $this->flowManager->transitionTo($phoneNumber, 'awaiting_payment_number', [
            'event_id' => $eventId,
            'payment_method' => $paymentMethod,
        ]);
    }

    /**
     * Process payment phone number and initiate payment
     */
    public function processPaymentNumber(string $phoneNumber, string $paymentPhoneNumber): void
    {
        $eventId = $this->flowManager->getContext($phoneNumber, 'event_id');
        $paymentMethod = $this->flowManager->getContext($phoneNumber, 'payment_method', 'mobile_money');

        if (!$eventId) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Session expired. Please start again."
            );
            $this->flowManager->clearFlow($phoneNumber);
            return;
        }

        $event = Event::find($eventId);

        if (!$event) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, this event is no longer available."
            );
            $this->flowManager->clearFlow($phoneNumber);
            return;
        }

        // Clean up phone number
        $cleanedPhone = $this->cleanPhoneNumber($paymentPhoneNumber);

        if (!$this->isValidPhoneNumber($cleanedPhone)) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "⚠️ Invalid phone number format. Please send a valid phone number.\n\nExample: 0771234567"
            );
            return;
        }

        // Initiate payment
        $this->whatsapp->sendTextMessage(
            $phoneNumber,
            "⏳ Processing your payment request...\n\nPlease wait a moment."
        );

        $result = $this->pesePayService->createSeamlessPayment($event, $cleanedPhone, $paymentMethod);

        if ($result['success']) {
            $message = "✅ *Payment Initiated*\n\n";
            $message .= "📱 Please check your phone ({$cleanedPhone}) and enter your PIN to complete the payment.\n\n";
            $message .= "Reference: {$result['reference_number']}\n";
            $message .= "Amount: \${$event->amount}\n\n";
            $message .= "⏱️ You have 2 minutes to complete the payment.";

            $this->whatsapp->sendTextMessage($phoneNumber, $message);

            // Store payment info in context
            $this->flowManager->transitionTo($phoneNumber, 'awaiting_payment_confirmation', [
                'event_id' => $eventId,
                'payment_id' => $result['payment']->id,
                'reference_number' => $result['reference_number'],
                'payment_phone' => $cleanedPhone,
            ]);

            // Dispatch job to poll payment status
            CheckPaymentStatus::dispatch($result['payment'])->delay(now()->addSeconds(5));

        } else {
            $errorMessage = "❌ *Payment Failed*\n\n";
            $errorMessage .= $result['message'] . "\n\n";
            $errorMessage = "Please try again or contact support if the problem persists.";

            $this->whatsapp->sendTextMessage($phoneNumber, $errorMessage);

            Log::error('Payment initiation failed', [
                'phone' => $phoneNumber,
                'event_id' => $eventId,
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            // Go back to payment method selection
            $this->flowManager->transitionTo($phoneNumber, 'selecting_payment_method', [
                'event_id' => $eventId,
            ]);
        }
    }

    /**
     * Complete enrollment after successful payment
     */
    public function completeEnrollment(Payment $payment): void
    {
        $event = $payment->event;
        $phoneNumber = $payment->phone_number;

        // Create enrollment
        $enrollment = Enrollment::create([
            'event_id' => $event->id,
            'phone_number' => $phoneNumber,
            'payment_id' => $payment->id,
            'status' => 'confirmed',
            'enrollment_date' => now(),
        ]);

        // Send confirmation message
        $message = "🎉 *Enrollment Confirmed!*\n\n";
        $message .= "You have successfully enrolled in:\n";
        $message .= "📚 *{$event->title}*\n\n";
        $message .= "📅 Category: {$event->category->name}\n";
        $message .= "💰 Amount Paid: \${$payment->amount}\n";
        $message .= "🎫 Enrollment ID: {$enrollment->id}\n\n";

        if ($event->type === 'course' && $event->schedules->isNotEmpty()) {
            $message .= "📆 *Upcoming Sessions:*\n";
            foreach ($event->schedules->take(3) as $schedule) {
                $message .= "• Session {$schedule->session_number}: {$schedule->start_date->format('M d, Y')} at {$schedule->start_time}\n";
            }
            $message .= "\n";
        } elseif ($event->start_date) {
            $message .= "📆 Date: {$event->start_date->format('M d, Y')} at {$event->start_time}\n";
            $message .= "📍 Location: {$event->location}\n\n";
        }

        $message .= "We'll send you reminders before the {$event->type} starts.\n\n";
        $message .= "Thank you for enrolling with Hustleprenure Academy! 🎓";

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        // Clear conversation flow
        $this->flowManager->clearFlow($phoneNumber);

        Log::info('Enrollment completed', [
            'enrollment_id' => $enrollment->id,
            'event_id' => $event->id,
            'phone' => $phoneNumber,
        ]);
    }

    /**
     * Handle failed payment
     */
    public function handleFailedPayment(Payment $payment): void
    {
        $phoneNumber = $payment->phone_number;
        $event = $payment->event;

        $message = "❌ *Payment Failed*\n\n";
        $message .= "Your payment for {$event->title} could not be processed.\n\n";
        $message .= "Reason: {$payment->failed_reason}\n\n";
        $message .= "Please try again or contact support if you need assistance.";

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        // Clear flow
        $this->flowManager->clearFlow($phoneNumber);

        Log::warning('Payment failed notification sent', [
            'payment_id' => $payment->id,
            'phone' => $phoneNumber,
            'reason' => $payment->failed_reason,
        ]);
    }

    /**
     * Show user's enrollments
     */
    public function showMyEnrollments(string $phoneNumber): void
    {
        $enrollments = Enrollment::where('phone_number', $phoneNumber)
            ->with(['event.category', 'event.instructors'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->latest()
            ->limit(10)
            ->get();

        if ($enrollments->isEmpty()) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "You don't have any active enrollments yet.\n\nBrowse our events and courses to get started!"
            );
            return;
        }

        $message = "📋 *My Enrollments*\n\n";

        foreach ($enrollments as $enrollment) {
            $event = $enrollment->event;
            $message .= "🎓 *{$event->title}*\n";
            $message .= "📂 {$event->category->name}\n";
            $message .= "📅 Enrolled: {$enrollment->enrollment_date->format('M d, Y')}\n";
            $message .= "Status: " . ucfirst($enrollment->status) . "\n";

            if ($event->type === 'course' && $event->schedules->isNotEmpty()) {
                $nextSession = $event->schedules()->where('start_date', '>=', now())->first();
                if ($nextSession) {
                    $message .= "📆 Next Session: {$nextSession->start_date->format('M d, Y')} at {$nextSession->start_time}\n";
                }
            }

            $message .= "\n";
        }

        $message .= "To view more details, browse to the specific event/course.";

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        $this->flowManager->transitionTo($phoneNumber, 'viewing_enrollments');
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, and parentheses
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Remove leading + or 00
        $cleaned = preg_replace('/^(\+|00)/', '', $cleaned);

        return $cleaned;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        // Basic validation: should be numeric and between 9-15 digits
        return preg_match('/^[0-9]{9,15}$/', $phone);
    }
}
