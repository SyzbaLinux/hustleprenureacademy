<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'enrollment_id',
        'phone_number',
        'amount',
        'currency',
        'payment_method',
        'reference_number',
        'poll_url',
        'transaction_id',
        'status',
        'paid_at',
        'failed_reason',
        'pesepay_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'pesepay_response' => 'array',
    ];

    /**
     * Get the event for this payment
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user for this payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the enrollment for this payment
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Scope paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
