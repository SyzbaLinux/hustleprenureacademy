<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'payment_id',
        'phone_number',
        'full_name',
        'email',
        'status',
        'enrollment_date',
        'completion_date',
        'certificate_issued',
        'certificate_url',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'completion_date' => 'datetime',
        'certificate_issued' => 'boolean',
    ];

    /**
     * Get the event for this enrollment
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user for this enrollment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment for this enrollment
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get reminders for this enrollment
     */
    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Scope confirmed enrollments
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope completed enrollments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
