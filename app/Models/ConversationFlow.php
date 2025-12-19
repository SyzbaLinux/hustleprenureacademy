<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConversationFlow extends Model
{
    protected $fillable = [
        'phone_number',
        'message_id',
        'current_state',
        'previous_state',
        'context_data',
        'last_interaction_at',
        'expires_at',
    ];

    protected $casts = [
        'context_data' => 'array',
        'last_interaction_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot method to set expiry automatically
     */
    protected static function booted()
    {
        static::creating(function ($flow) {
            if (!$flow->expires_at) {
                $flow->expires_at = Carbon::now()->addMinutes(config('whatsapp.conversation_timeout', 30));
            }
        });

        static::updating(function ($flow) {
            $flow->last_interaction_at = Carbon::now();
            $flow->expires_at = Carbon::now()->addMinutes(config('whatsapp.conversation_timeout', 30));
        });
    }

    /**
     * Get a specific context value
     */
    public function getContext($key, $default = null)
    {
        return data_get($this->context_data, $key, $default);
    }

    /**
     * Set a specific context value
     */
    public function setContext($key, $value)
    {
        $context = $this->context_data ?? [];
        data_set($context, $key, $value);
        $this->context_data = $context;
        return $this;
    }

    /**
     * Clear all context data
     */
    public function clearContext()
    {
        $this->context_data = null;
        return $this;
    }

    /**
     * Scope expired flows
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    /**
     * Scope active flows
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>=', Carbon::now());
    }
}
