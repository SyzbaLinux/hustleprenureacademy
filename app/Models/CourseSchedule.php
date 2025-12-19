<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    protected $fillable = [
        'event_id',
        'session_number',
        'title',
        'description',
        'start_date',
        'start_time',
        'end_time',
        'is_completed',
    ];

    protected $casts = [
        'session_number' => 'integer',
        'start_date' => 'date',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the event (course) for this schedule
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get reminders for this schedule
     */
    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'schedule_id');
    }
}
