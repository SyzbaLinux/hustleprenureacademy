<?php

namespace App\Services\WhatsApp;

use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Event;
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
                    'link' => url('storage/'.$event->flier),
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
            $body .= '📅 Date: '.($event->published_at ? $event->published_at->format('M d, Y') : 'TBA')."\n";
        } else {
            // Course with multiple sessions
            $body .= "📅 Course Duration: {$event->duration_hours} hours\n";
            if ($event->schedules->isNotEmpty()) {
                $body .= '📅 Starts: '.$event->schedules->first()->start_date->format('M d, Y')."\n";
            }
        }

        $body .= '📍 Location: '.($event->location_type === 'online' ? 'Online' : $event->location)."\n";
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
        $message .= '📅 Enrolled on: '.$enrollment->enrollment_date->format('M d, Y')."\n";
        $message .= '📊 Status: '.ucfirst($enrollment->status)."\n";

        if ($enrollment->payment) {
            $message .= '💰 Payment: '.ucfirst($enrollment->payment->status)."\n";
            if ($enrollment->payment->status === 'paid') {
                $message .= "✅ Paid: \${$enrollment->payment->amount} on ".$enrollment->payment->paid_at->format('M d, Y')."\n";
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
     * Build welcome message with numbered menu
     */
    public function buildWelcomeMessage(?string $userName = null): string
    {
        $greeting = $userName ? "{$userName}, welcome back to Hustleprenure Academy! 🎓" : 'Welcome to Hustleprenure Academy! 🎓';

        $message = "{$greeting}\n\n";
        $message .= "  1. Browse Events 📅\n";
        $message .= "  2. Browse Courses 📚\n";
        $message .= "  3. My Enrollments 📋\n\n";
        $message .= "💬 Reply with a number (e.g., '1')";

        return $message;
    }

    /**
     * Build category numbered list message
     */
    public function buildCategoryNumberedList(Collection $categories, string $type = 'event'): string
    {
        $typeLabel = $type === 'event' ? 'Events' : 'Courses';
        $message = "📂 *Select a Category for {$typeLabel}*\n\n";

        $index = 1;
        foreach ($categories as $category) {
            $message .= "  {$index}. {$category->name}\n";
            $index++;
        }

        $message .= "\n💬 Reply with a number (e.g., '1') or type 'menu' to go back";

        return $message;
    }

    /**
     * Build event numbered list message
     */
    public function buildEventNumberedList(Collection $events, string $type = 'event'): string
    {
        $typeLabel = $type === 'event' ? 'Events' : 'Courses';
        $categoryName = $events->first()?->category->name ?? 'this category';

        $message = "📅 *Available {$typeLabel} in {$categoryName}*\n\n";

        $index = 1;
        foreach ($events as $event) {
            // Title
            $message .= "  *{$index}. {$event->title}*\n";

            // Date/Duration
            if ($event->type === 'event') {
                $date = $event->published_at ? $event->published_at->format('M d, Y') : 'TBA';
                $message .= "     📅 {$date}\n";
            } else {
                // For courses, show date range if schedules exist
                if ($event->schedules->isNotEmpty()) {
                    $firstSchedule = $event->schedules->first();
                    $lastSchedule = $event->schedules->last();
                    $startDate = $firstSchedule->start_date->format('d M Y');
                    $endDate = $lastSchedule->start_date->format('d M Y');
                    $message .= "      {$startDate}-{$endDate}\n";
                }
            }

            // Location
            $location = $event->location_type === 'online' ? 'Online' : ($event->location ?? 'TBA');
            $message .= "      {$location}\n";

            // Price
            $message .= "      \${$event->amount}\n\n";

            $index++;
        }

        $message .= "💬 Reply with a number (e.g., '1') to see options, or type 'menu' to go back";

        return $message;
    }

    /**
     * Build payment confirmation buttons
     */
    public function buildPaymentConfirmationButtons(): array
    {
        return [
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'check_payment_status',
                    'title' => '🔄 Check Status',
                ],
            ],
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'back_to_main',
                    'title' => 'Back to Menu 🏠',
                ],
            ],
        ];
    }

    /**
     * Build event action menu message
     */
    public function buildEventActionMenu(Event $event): string
    {
        $message = "🌟 *{$event->title}*\n\n";
        $message .= "📂 Category: {$event->category->name}\n";

        if ($event->instructors->isNotEmpty()) {
            $message .= '👨‍🏫 Instructor(s): '.$event->instructors->pluck('name')->join(', ')."\n";
        }

        if ($event->type === 'event') {
            $message .= '📅 Date: '.($event->published_at ? $event->published_at->format('M d, Y') : 'TBA')."\n";
        } else {
            $message .= "📅 Course Duration: {$event->duration_hours} hours\n";
            if ($event->schedules->isNotEmpty()) {
                $message .= '📅 Starts: '.$event->schedules->first()->start_date->format('M d, Y')."\n";
            }
        }

        $message .= '📍 Location: '.($event->location_type === 'online' ? 'Online' : $event->location)."\n";
        $message .= "💰 Price: \${$event->amount}\n\n";

        // Short description
        $description = $event->short_description ?? $event->description;
        $message .= substr($description, 0, 150)."...\n\n";

        // Numbered action options
        $message .= "*What would you like to do?*\n";
        $message .= "  1. View Full Details\n";
        $message .= "  2. Enroll & Pay 💳\n";
        $message .= "  3. Back to Events\n\n";
        $message .= '💬 Reply with a number (1-3)';

        return $message;
    }
}
