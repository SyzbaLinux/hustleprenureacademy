<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\Reminder\ReminderService;
use Illuminate\Support\Facades\Log;

class EnrollmentObserver
{
    protected $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Handle the Enrollment "created" event.
     */
    public function created(Enrollment $enrollment): void
    {
        // Only schedule reminders for confirmed enrollments
        if ($enrollment->status === 'confirmed') {
            Log::info('New enrollment created, scheduling reminders', [
                'enrollment_id' => $enrollment->id,
                'event_id' => $enrollment->event_id,
            ]);

            try {
                $this->reminderService->scheduleEnrollmentReminders($enrollment);
            } catch (\Exception $e) {
                Log::error('Failed to schedule reminders for enrollment', [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Enrollment "updated" event.
     */
    public function updated(Enrollment $enrollment): void
    {
        // If status changed to confirmed, schedule reminders
        if ($enrollment->isDirty('status') && $enrollment->status === 'confirmed') {
            Log::info('Enrollment status changed to confirmed, scheduling reminders', [
                'enrollment_id' => $enrollment->id,
            ]);

            try {
                $this->reminderService->scheduleEnrollmentReminders($enrollment);
            } catch (\Exception $e) {
                Log::error('Failed to schedule reminders after status update', [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // If status changed to cancelled, cancel reminders
        if ($enrollment->isDirty('status') && $enrollment->status === 'cancelled') {
            Log::info('Enrollment cancelled, removing reminders', [
                'enrollment_id' => $enrollment->id,
            ]);

            try {
                $this->reminderService->cancelEnrollmentReminders($enrollment);
            } catch (\Exception $e) {
                Log::error('Failed to cancel reminders', [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Enrollment "deleted" event.
     */
    public function deleted(Enrollment $enrollment): void
    {
        Log::info('Enrollment deleted, cancelling reminders', [
            'enrollment_id' => $enrollment->id,
        ]);

        try {
            $this->reminderService->cancelEnrollmentReminders($enrollment);
        } catch (\Exception $e) {
            Log::error('Failed to cancel reminders after deletion', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
