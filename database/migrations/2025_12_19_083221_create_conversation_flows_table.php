<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_flows', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('message_id')->nullable();
            $table->enum('current_state', [
                'idle',
                'main_menu',
                'browsing_categories',
                'browsing_events',
                'browsing_courses',
                'viewing_event_details',
                'selecting_payment_method',
                'awaiting_payment',
                'payment_confirmation',
                'registration_name',
                'registration_email',
                'registration_complete'
            ])->default('idle');
            $table->string('previous_state')->nullable();
            $table->json('context_data')->nullable();
            $table->timestamp('last_interaction_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('phone_number');
            $table->index('current_state');
            $table->index('last_interaction_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_flows');
    }
};
