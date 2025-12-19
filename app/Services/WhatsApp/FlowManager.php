<?php

namespace App\Services\WhatsApp;

use App\Models\ConversationFlow;
use Carbon\Carbon;

class FlowManager
{
    /**
     * Get current flow for a phone number
     */
    public function getCurrentFlow(string $phoneNumber): ?ConversationFlow
    {
        return ConversationFlow::where('phone_number', $phoneNumber)
            ->active()
            ->latest()
            ->first();
    }

    /**
     * Update or create flow
     */
    public function updateFlow(string $phoneNumber, string $state, array $context = []): ConversationFlow
    {
        $flow = $this->getCurrentFlow($phoneNumber);

        if ($flow) {
            $flow->previous_state = $flow->current_state;
            $flow->current_state = $state;

            // Merge context data
            if (!empty($context)) {
                $existingContext = $flow->context_data ?? [];
                $flow->context_data = array_merge($existingContext, $context);
            }

            $flow->last_interaction_at = Carbon::now();
            $flow->expires_at = Carbon::now()->addMinutes(config('whatsapp.conversation_timeout', 30));
            $flow->save();
        } else {
            $flow = ConversationFlow::create([
                'phone_number' => $phoneNumber,
                'current_state' => $state,
                'previous_state' => null,
                'context_data' => $context,
                'last_interaction_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes(config('whatsapp.conversation_timeout', 30)),
            ]);
        }

        return $flow;
    }

    /**
     * Transition to a new state
     */
    public function transitionTo(string $phoneNumber, string $newState, array $context = []): ConversationFlow
    {
        return $this->updateFlow($phoneNumber, $newState, $context);
    }

    /**
     * Clear flow (conversation ended)
     */
    public function clearFlow(string $phoneNumber): void
    {
        ConversationFlow::where('phone_number', $phoneNumber)->delete();
    }

    /**
     * Get context value
     */
    public function getContext(string $phoneNumber, string $key, $default = null)
    {
        $flow = $this->getCurrentFlow($phoneNumber);

        if (!$flow) {
            return $default;
        }

        return $flow->getContext($key, $default);
    }

    /**
     * Set context value
     */
    public function setContext(string $phoneNumber, string $key, $value): void
    {
        $flow = $this->getCurrentFlow($phoneNumber);

        if ($flow) {
            $flow->setContext($key, $value);
            $flow->save();
        }
    }

    /**
     * Get previous state
     */
    public function getPreviousState(string $phoneNumber): ?string
    {
        $flow = $this->getCurrentFlow($phoneNumber);
        return $flow?->previous_state;
    }

    /**
     * Go back to previous state
     */
    public function goBack(string $phoneNumber): ?ConversationFlow
    {
        $flow = $this->getCurrentFlow($phoneNumber);

        if ($flow && $flow->previous_state) {
            $newState = $flow->previous_state;
            $flow->current_state = $newState;
            $flow->previous_state = null;
            $flow->save();

            return $flow;
        }

        return null;
    }

    /**
     * Check if flow is in a specific state
     */
    public function isInState(string $phoneNumber, string $state): bool
    {
        $flow = $this->getCurrentFlow($phoneNumber);
        return $flow && $flow->current_state === $state;
    }

    /**
     * Clean up expired flows
     */
    public function cleanupExpiredFlows(): int
    {
        return ConversationFlow::expired()->delete();
    }
}
