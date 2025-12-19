<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'enrollment_id',
        'event_id',
        'schedule_id',
        'phone_number',
        'reminder_type',
        'message',
        'scheduled_for',
        'sent_at',
        'status',
        'whatsapp_message_id',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Get the enrollment for this reminder
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the event for this reminder
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the schedule for this reminder
     */
    public function schedule()
    {
        return $this->belongsTo(CourseSchedule::class, 'schedule_id');
    }

    /**
     * Scope pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope sent reminders
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope due reminders
     */
    public function scopeDue($query)
    {
        return $query->where('status', 'pending')
            ->where('scheduled_for', '<=', now());
    }
}
