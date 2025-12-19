<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'bio',
        'profile_photo',
        'specialization',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the events for this instructor
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_instructor')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Scope active instructors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
