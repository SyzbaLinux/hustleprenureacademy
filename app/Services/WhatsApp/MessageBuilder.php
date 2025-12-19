<?php

namespace App\Services\WhatsApp;

use App\Models\Event;
use App\Models\Category;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

class MessageBuilder
{
    /**
     * Build main menu buttons
     */
    public function buildMainMenu(): array
    {
        return [
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'browse_events',
                    'title' => 'Browse Events 📅',
                ],
            ],
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'browse_courses',
                    'title' => 'Browse Courses 📚',
                ],
            ],
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'my_enrollments',
                    'title' => 'My Enrollments 📋',
                ],
            ],
        ];
    }

    /**
     * Build category list
     */
    public function buildCategoryList(Collection $categories): array
    {
        $rows = $categories->map(function ($category) {
            return [
                'id' => "category_{$category->id}",
                'title' => $category->name,
                'description' => substr($category->description ?? '', 0, 72),
            ];
        })->toArray();

        return [
            [
                'title' => 'Categories',
                'rows' => $rows,
            ],
        ];
    }

    /**
     * Build event card with buttons
     */
    public function buildEventCard(Event $event): array
    {
        // Prepare header with image if available
        $header = null;
        if ($event->flier) {
            $header = [
                'type' => 'image',
                'image' => [
                    'link' => url('storage/' . $event->flier),
                ],
            ];
        }

        // Build body message
        $body = "🌟 *{$event->title}*\n\n";
        $body .= "📚 Category: {$event->category->name}\n";

        if ($event->instructors->isNotEmpty()) {
            $instructorNames = $event->instructors->pluck('name')->join(', ');
            $body .= "👨‍🏫 Instructor(s): {$instructorNames}\n";
        }

        if ($event->type === 'event') {
            // Single event
            $body .= "📅 Date: " . ($event->published_at ? $event->published_at->format('M d, Y') : 'TBA') . "\n";
        } else {
            // Course with multiple sessions
            $body .= "📅 Course Duration: {$event->duration_hours} hours\n";
            if ($event->schedules->isNotEmpty()) {
                $body .= "📅 Starts: " . $event->schedules->first()->start_date->format('M d, Y') . "\n";
            }
        }

        $body .= "📍 Location: " . ($event->location_type === 'online' ? 'Online' : $event->location) . "\n";
        $body .= "💰 Price: \${$event->amount}\n\n";
        $body .= substr($event->short_description ?? $event->description, 0, 200);

        // Build buttons
        $buttons = [
            [
                'type' => 'reply',
                'reply' => [
                    'id' => "view_event_{$event->id}",
                    'title' => 'View More Details',
                ],
            ],
            [
                'type' => 'reply',
                'reply' => [
                    'id' => "enroll_event_{$event->id}",
                    'title' => 'Enroll & Pay 💳',
                ],
            ],
        ];

        return [
            'header' => $header,
            'body' => $body,
            'buttons' => $buttons,
        ];
    }

    /**
     * Build payment method buttons
     */
    public function buildPaymentMethodButtons(): array
    {
        return [
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'payment_ecocash',
                    'title' => 'EcoCash 💰',
                ],
            ],
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'payment_onemoney',
                    'title' => 'OneMoney 💸',
                ],
            ],
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'back_to_main',
                    'title' => 'Back 🔙',
                ],
            ],
        ];
    }

    /**
     * Build enrollment details message
     */
    public function buildEnrollmentDetails(Enrollment $enrollment): string
    {
        $message = "📋 *Enrollment Details*\n\n";
        $message .= "📚 Event/Course: {$enrollment->event->title}\n";
        $message .= "📅 Enrolled on: " . $enrollment->enrollment_date->format('M d, Y') . "\n";
        $message .= "📊 Status: " . ucfirst($enrollment->status) . "\n";

        if ($enrollment->payment) {
            $message .= "💰 Payment: " . ucfirst($enrollment->payment->status) . "\n";
            if ($enrollment->payment->status === 'paid') {
                $message .= "✅ Paid: \${$enrollment->payment->amount} on " . $enrollment->payment->paid_at->format('M d, Y') . "\n";
            }
        }

        if ($enrollment->event->type === 'course' && $enrollment->event->schedules->isNotEmpty()) {
            $message .= "\n📅 *Upcoming Sessions:*\n";
            foreach ($enrollment->event->schedules->take(3) as $schedule) {
                $message .= "  • Session {$schedule->session_number}: {$schedule->start_date->format('M d')} at {$schedule->start_time}\n";
            }
        }

        return $message;
    }

    /**
     * Build welcome message
     */
    public function buildWelcomeMessage(): string
    {
        return "Welcome to Hustleprenure Academy! 🎓\n\n" .
               "We offer professional events and courses to help you grow your skills.\n\n" .
               "What would you like to do?";
    }
}
