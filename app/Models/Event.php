<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'type',
        'description',
        'short_description',
        'flier',
        'location',
        'location_type',
        'meeting_link',
        'capacity',
        'amount',
        'currency',
        'duration_hours',
        'is_active',
        'is_featured',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'capacity' => 'integer',
        'duration_hours' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get the category for this event
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the instructors for this event
     */
    public function instructors()
    {
        return $this->belongsToMany(Instructor::class, 'event_instructor')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the schedules for this course
     */
    public function schedules()
    {
        return $this->hasMany(CourseSchedule::class)->orderBy('session_number');
    }

    /**
     * Get the prerequisites for this course
     */
    public function prerequisites()
    {
        return $this->hasMany(CoursePrerequisite::class, 'course_id');
    }

    /**
     * Get enrollments for this event
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get payments for this event
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get reminders for this event
     */
    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Get the user who created this event
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope active events
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope published events
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope featured events
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope events by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('published_at', '>', now());
    }
}
