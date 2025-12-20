<?php

namespace App\Services\Chatbot;

use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Event;
use App\Services\WhatsApp\FlowManager;
use App\Services\WhatsApp\MessageBuilder;
use App\Services\WhatsApp\WhatsAppService;

class EventBrowserService
{
    protected $whatsapp;

    protected $messageBuilder;

    protected $flowManager;

    public function __construct(
        WhatsAppService $whatsapp,
        MessageBuilder $messageBuilder,
        FlowManager $flowManager
    ) {
        $this->whatsapp = $whatsapp;
        $this->messageBuilder = $messageBuilder;
        $this->flowManager = $flowManager;
    }

    /**
     * Show categories for browsing
     */
    public function showCategories(string $phoneNumber, string $type = 'event'): void
    {
        $categories = Category::active()
            ->ordered()
            ->whereHas('events', function ($query) use ($type) {
                $query->where('type', $type)
                    ->active()
                    ->published()
                    ->available();
            })
            ->get();

        if ($categories->isEmpty()) {
            $typeLabel = $type === 'event' ? 'events' : 'courses';
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, there are no categories with available {$typeLabel} at the moment. Please check back later."
            );

            return;
        }

        $message = $this->messageBuilder->buildCategoryNumberedList($categories, $type);

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        $this->flowManager->transitionTo($phoneNumber, 'browsing_categories', [
            'type' => $type,
            'categories' => $categories->pluck('id')->toArray(),
        ]);
    }

    /**
     * Show events/courses by category
     */
    public function showEventsByCategory(string $phoneNumber, int $categoryId): void
    {
        $type = $this->flowManager->getContext($phoneNumber, 'type', 'event');

        $events = Event::where('category_id', $categoryId)
            ->where('type', $type)
            ->active()
            ->published()
            ->available()
            ->with(['category', 'instructors', 'schedules'])
            ->latest('published_at')
            ->limit(10)
            ->get();

        if ($events->isEmpty()) {
            $category = Category::find($categoryId);
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, there are no available {$type}s in the {$category->name} category at the moment.\n\nType 'menu' to go back."
            );

            return;
        }

        // Send numbered list instead of individual cards
        $message = $this->messageBuilder->buildEventNumberedList($events, $type);
        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        // Store event IDs in context
        $this->flowManager->transitionTo($phoneNumber, 'browsing_'.$type.'s', [
            'category_id' => $categoryId,
            'type' => $type,
            'events' => $events->pluck('id')->toArray(),
        ]);
    }

    /**
     * Show event action menu
     */
    public function showEventActionMenu(string $phoneNumber, int $eventId): void
    {
        $event = Event::with(['category', 'instructors', 'schedules'])
            ->find($eventId);

        if (! $event) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                'Sorry, this event/course is no longer available.'
            );

            return;
        }

        $message = $this->messageBuilder->buildEventActionMenu($event);
        $this->whatsapp->sendTextMessage($phoneNumber, $message);

        $this->flowManager->transitionTo($phoneNumber, 'selected_event', [
            'event_id' => $eventId,
        ]);
    }

    /**
     * Show full event details
     */
    public function showEventDetails(string $phoneNumber, int $eventId): void
    {
        $event = Event::with(['category', 'instructors', 'schedules', 'prerequisites.prerequisiteCourse'])
            ->find($eventId);

        if (! $event) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                'Sorry, this event/course is no longer available.'
            );

            return;
        }

        $message = "📚 *Full Details*\n\n";
        $message .= "🌟 *{$event->title}*\n\n";
        $message .= "📂 Category: {$event->category->name}\n";

        if ($event->instructors->isNotEmpty()) {
            $message .= '👨‍🏫 Instructor(s): '.$event->instructors->pluck('name')->join(', ')."\n";
        }

        $message .= '📍 Location: ';
        if ($event->location_type === 'online') {
            $message .= "Online\n";
        } elseif ($event->location_type === 'hybrid') {
            $message .= "Hybrid (Online & {$event->location})\n";
        } else {
            $message .= "{$event->location}\n";
        }

        $message .= "💰 Price: \${$event->amount}\n";

        if ($event->capacity) {
            $enrolledCount = $event->enrollments()->confirmed()->count();
            $spotsLeft = $event->capacity - $enrolledCount;
            $message .= "📊 Spots Available: {$spotsLeft} / {$event->capacity}\n";
        }

        $message .= "\n📝 *Description:*\n{$event->description}\n";

        // Show course-specific details
        if ($event->type === 'course' && $event->schedules->isNotEmpty()) {
            $message .= "\n📅 *Course Schedule:*\n";
            foreach ($event->schedules as $schedule) {
                $message .= "Session {$schedule->session_number}: {$schedule->title}\n";
                $message .= "  📅 {$schedule->start_date->format('M d, Y')} at {$schedule->start_time}\n";
            }
            $message .= "\n⏱️ Total Duration: {$event->duration_hours} hours\n";
        }

        // Show prerequisites if any
        if ($event->type === 'course' && $event->prerequisites->isNotEmpty()) {
            $message .= "\n⚠️ *Prerequisites:*\n";
            foreach ($event->prerequisites as $prereq) {
                $marker = $prereq->is_required ? '✓' : '○';
                $message .= "  {$marker} {$prereq->prerequisiteCourse->title}\n";
            }
        }

        // Build buttons for actions
        $buttons = [
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'back_to_main',
                    'title' => 'Main Menu',
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

        $this->whatsapp->sendInteractiveButtons($phoneNumber, $buttons, $message);

        $this->flowManager->transitionTo($phoneNumber, 'viewing_event_details', [
            'event_id' => $eventId,
        ]);
    }

    /**
     * Check if user meets prerequisites for a course
     */
    public function checkPrerequisites(string $phoneNumber, int $courseId): bool
    {
        $course = Event::with('prerequisites.prerequisiteCourse')->find($courseId);

        if (! $course || $course->prerequisites->isEmpty()) {
            return true; // No prerequisites
        }

        // Get user's completed enrollments
        $completedCourses = Enrollment::where('phone_number', $phoneNumber)
            ->where('status', 'completed')
            ->pluck('event_id')
            ->toArray();

        $missingRequired = [];

        foreach ($course->prerequisites as $prereq) {
            if ($prereq->is_required && ! in_array($prereq->prerequisite_course_id, $completedCourses)) {
                $missingRequired[] = $prereq->prerequisiteCourse->title;
            }
        }

        if (! empty($missingRequired)) {
            $message = "⚠️ *Prerequisites Not Met*\n\n";
            $message .= "To enroll in this course, you must first complete:\n\n";
            foreach ($missingRequired as $title) {
                $message .= "  • {$title}\n";
            }
            $message .= "\nPlease complete these courses first before enrolling.";

            $this->whatsapp->sendTextMessage($phoneNumber, $message);

            return false;
        }

        return true;
    }
}
