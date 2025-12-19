<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePrerequisite extends Model
{
    protected $fillable = [
        'course_id',
        'prerequisite_course_id',
        'is_required',
        'description',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Get the course that has this prerequisite
     */
    public function course()
    {
        return $this->belongsTo(Event::class, 'course_id');
    }

    /**
     * Get the prerequisite course
     */
    public function prerequisiteCourse()
    {
        return $this->belongsTo(Event::class, 'prerequisite_course_id');
    }
}
