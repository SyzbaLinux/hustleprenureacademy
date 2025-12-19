<?php

namespace App\Services\Reminder;

use App\Models\Enrollment;
use App\Models\Reminder;
use App\Models\Event;
use App\Models\CourseSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    /**
     * Schedule all reminders for an enrollment
     */
    public function scheduleEnrollmentReminders(Enrollment $enrollment): void
    {
        $event = $enrollment->event;

        Log::info('Scheduling reminders for enrollment', [
            'enrollment_id' => $enrollment->id,
            'event_id' => $event->id,
            'event_type' => $event->type,
        ]);

        // 1. Send enrollment confirmation reminder immediately
        $this->createReminder(
            $enrollment,
            'enrollment_confirmation',
            now(),
            $this->buildEnrollmentConfirmationMessage($enrollment)
        );

        if ($event->type === 'event') {
            // For one-time events
            $this->scheduleEventReminders($enrollment);
        } else {
            // For courses with multiple sessions
            $this->scheduleCourseReminders($enrollment);
        }
    }

    /**
     * Schedule reminders for a one-time event
     */
    private function scheduleEventReminders(Enrollment $enrollment): void
    {
        $event = $enrollment->event;

        if (!$event->start_date || !$event->start_time) {
            Log::warning('Event missing start date/time, skipping reminders', [
                'event_id' => $event->id,
            ]);
            return;
        }

        $eventStartDateTime = Carbon::parse($event->start_date . ' ' . $event->start_time);

        // 1 day before reminder
        $oneDayBefore = $eventStartDateTime->copy()->subDay();
        if ($oneDayBefore->isFuture()) {
            $this->createReminder(
                $enrollment,
                'event_1day_before',
                $oneDayBefore,
                $this->buildEventReminderMessage($event, '24 hours')
            );
        }

        // 1 hour before reminder
        $oneHourBefore = $eventStartDateTime->copy()->subHour();
        if ($oneHourBefore->isFuture()) {
            $this->createReminder(
                $enrollment,
                'event_1hour_before',
                $oneHourBefore,
                $this->buildEventReminderMessage($event, '1 hour')
            );
        }
    }

    /**
     * Schedule reminders for a course with multiple sessions
     */
    private function scheduleCourseReminders(Enrollment $enrollment): void
    {
        $event = $enrollment->event;
        $schedules = $event->schedules()->orderBy('session_number')->get();

        if ($schedules->isEmpty()) {
            Log::warning('Course has no schedules, skipping session reminders', [
                'event_id' => $event->id,
            ]);
            return;
        }

        foreach ($schedules as $schedule) {
            $this->scheduleSessionReminders($enrollment, $schedule);
        }

        // Course completion reminder after last session
        $lastSession = $schedules->last();
        if ($lastSession) {
            $completionTime = Carbon::parse($lastSession->start_date . ' ' . $lastSession->end_time);
            $completionTime->addHours(1); // 1 hour after last session ends

            if ($completionTime->isFuture()) {
                $this->createReminder(
                    $enrollment,
                    'course_completion',
                    $completionTime,
                    $this->buildCourseCompletionMessage($event),
                    $lastSession->id
                );
            }
        }
    }

    /**
     * Schedule reminders for a course session
     */
    private function scheduleSessionReminders(Enrollment $enrollment, CourseSchedule $schedule): void
    {
        $sessionDateTime = Carbon::parse($schedule->start_date . ' ' . $schedule->start_time);

        // 1 day before session
        $oneDayBefore = $sessionDateTime->copy()->subDay();
        if ($oneDayBefore->isFuture()) {
            $this->createReminder(
                $enrollment,
                'course_session_1day_before',
                $oneDayBefore,
                $this->buildSessionReminderMessage($schedule, '24 hours'),
                $schedule->id
            );
        }

        // 1 hour before session
        $oneHourBefore = $sessionDateTime->copy()->subHour();
        if ($oneHourBefore->isFuture()) {
            $this->createReminder(
                $enrollment,
                'course_session_1hour_before',
                $oneHourBefore,
                $this->buildSessionReminderMessage($schedule, '1 hour'),
                $schedule->id
            );
        }
    }

    /**
     * Create a reminder record
     */
    private function createReminder(
        Enrollment $enrollment,
        string $type,
        Carbon $scheduledFor,
        string $message,
        ?int $scheduleId = null
    ): Reminder {
        return Reminder::create([
            'enrollment_id' => $enrollment->id,
            'event_id' => $enrollment->event_id,
            'schedule_id' => $scheduleId,
            'phone_number' => $enrollment->phone_number,
            'reminder_type' => $type,
            'message' => $message,
            'scheduled_for' => $scheduledFor,
            'status' => 'pending',
        ]);
    }

    /**
     * Build enrollment confirmation message
     */
    private function buildEnrollmentConfirmationMessage(Enrollment $enrollment): string
    {
        $event = $enrollment->event;

        $message = "🎉 *Enrollment Confirmed!*\n\n";
        $message .= "Thank you for enrolling in:\n";
        $message .= "📚 *{$event->title}*\n\n";

        if ($event->type === 'course') {
            $message .= "This is a multi-session course. We'll send you reminders before each session.\n\n";
        } else {
            if ($event->start_date) {
                $message .= "📅 Date: {$event->start_date->format('l, F d, Y')}\n";
                $message .= "⏰ Time: {$event->start_time}\n\n";
            }
        }

        $message .= "We're excited to see you there! 🎓";

        return $message;
    }

    /**
     * Build event reminder message
     */
    private function buildEventReminderMessage(Event $event, string $timeframe): string
    {
        $message = "⏰ *Event Reminder*\n\n";
        $message .= "Your event is starting in {$timeframe}!\n\n";
        $message .= "📚 *{$event->title}*\n";
        $message .= "📅 {$event->start_date->format('l, F d, Y')}\n";
        $message .= "⏰ {$event->start_time}\n";

        if ($event->location_type === 'online') {
            $message .= "📍 Online Event\n";
            if ($event->meeting_link) {
                $message .= "🔗 Link: {$event->meeting_link}\n";
            }
        } elseif ($event->location_type === 'hybrid') {
            $message .= "📍 Hybrid: {$event->location}\n";
            if ($event->meeting_link) {
                $message .= "🔗 Online Link: {$event->meeting_link}\n";
            }
        } else {
            $message .= "📍 {$event->location}\n";
        }

        $message .= "\nSee you there! 🎓";

        return $message;
    }

    /**
     * Build session reminder message
     */
    private function buildSessionReminderMessage(CourseSchedule $schedule, string $timeframe): string
    {
        $event = $schedule->event;

        $message = "⏰ *Course Session Reminder*\n\n";
        $message .= "Your session is starting in {$timeframe}!\n\n";
        $message .= "📚 *{$event->title}*\n";
        $message .= "Session {$schedule->session_number}: {$schedule->title}\n\n";
        $message .= "📅 {$schedule->start_date->format('l, F d, Y')}\n";
        $message .= "⏰ {$schedule->start_time} - {$schedule->end_time}\n";

        if ($event->location_type === 'online') {
            $message .= "📍 Online Session\n";
            if ($event->meeting_link) {
                $message .= "🔗 Link: {$event->meeting_link}\n";
            }
        } elseif ($event->location_type === 'hybrid') {
            $message .= "📍 Hybrid: {$event->location}\n";
            if ($event->meeting_link) {
                $message .= "🔗 Online Link: {$event->meeting_link}\n";
            }
        } else {
            $message .= "📍 {$event->location}\n";
        }

        if ($schedule->description) {
            $message .= "\n📝 Topic: {$schedule->description}\n";
        }

        $message .= "\nReady to learn! 🎓";

        return $message;
    }

    /**
     * Build course completion message
     */
    private function buildCourseCompletionMessage(Event $event): string
    {
        $message = "🎊 *Congratulations!*\n\n";
        $message .= "You've completed the course:\n";
        $message .= "📚 *{$event->title}*\n\n";
        $message .= "Your certificate will be ready soon. We'll notify you once it's available for download.\n\n";
        $message .= "Thank you for learning with Hustleprenure Academy! 🎓\n\n";
        $message .= "Keep an eye out for more courses and events!";

        return $message;
    }

    /**
     * Cancel all reminders for an enrollment
     */
    public function cancelEnrollmentReminders(Enrollment $enrollment): void
    {
        Reminder::where('enrollment_id', $enrollment->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        Log::info('Cancelled reminders for enrollment', [
            'enrollment_id' => $enrollment->id,
        ]);
    }
}
