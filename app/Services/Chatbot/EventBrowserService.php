<?php

namespace App\Services\Chatbot;

use App\Models\Category;
use App\Models\Event;
use App\Models\Enrollment;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\WhatsApp\MessageBuilder;
use App\Services\WhatsApp\FlowManager;

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
        $categories = Category::active()->ordered()->get();

        if ($categories->isEmpty()) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, there are no categories available at the moment. Please check back later."
            );
            return;
        }

        $sections = $this->messageBuilder->buildCategoryList($categories);

        $this->whatsapp->sendInteractiveList(
            $phoneNumber,
            $sections,
            "Select a category to browse " . ($type === 'event' ? 'events' : 'courses') . ":",
            'Select Category'
        );

        $this->flowManager->transitionTo($phoneNumber, 'browsing_categories', [
            'type' => $type,
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
            ->with(['category', 'instructors'])
            ->latest('published_at')
            ->limit(5)
            ->get();

        if ($events->isEmpty()) {
            $category = Category::find($categoryId);
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, there are no {$type}s available in the {$category->name} category at the moment."
            );
            return;
        }

        // Send each event as a separate card
        foreach ($events as $event) {
            $eventCard = $this->messageBuilder->buildEventCard($event);

            $this->whatsapp->sendInteractiveButtons(
                $phoneNumber,
                $eventCard['buttons'],
                $eventCard['body'],
                $eventCard['header']
            );
        }

        $this->flowManager->transitionTo($phoneNumber, 'browsing_' . $type . 's', [
            'category_id' => $categoryId,
            'type' => $type,
        ]);
    }

    /**
     * Show full event details
     */
    public function showEventDetails(string $phoneNumber, int $eventId): void
    {
        $event = Event::with(['category', 'instructors', 'schedules', 'prerequisites.prerequisiteCourse'])
            ->find($eventId);

        if (!$event) {
            $this->whatsapp->sendTextMessage(
                $phoneNumber,
                "Sorry, this event/course is no longer available."
            );
            return;
        }

        $message = "📚 *Full Details*\n\n";
        $message .= "🌟 *{$event->title}*\n\n";
        $message .= "📂 Category: {$event->category->name}\n";

        if ($event->instructors->isNotEmpty()) {
            $message .= "👨‍🏫 Instructor(s): " . $event->instructors->pluck('name')->join(', ') . "\n";
        }

        $message .= "📍 Location: ";
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

        $this->whatsapp->sendTextMessage($phoneNumber, $message);

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

        if (!$course || $course->prerequisites->isEmpty()) {
            return true; // No prerequisites
        }

        // Get user's completed enrollments
        $completedCourses = Enrollment::where('phone_number', $phoneNumber)
            ->where('status', 'completed')
            ->pluck('event_id')
            ->toArray();

        $missingRequired = [];

        foreach ($course->prerequisites as $prereq) {
            if ($prereq->is_required && !in_array($prereq->prerequisite_course_id, $completedCourses)) {
                $missingRequired[] = $prereq->prerequisiteCourse->title;
            }
        }

        if (!empty($missingRequired)) {
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
